<?php

namespace Spatie\Stats;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Stats\Models\StatsEvent;
use Spatie\Stats\Models\StatsSnapshot;

class Stats
{
    protected BaseStats $statistic;

    protected string $period;

    protected DateTimeInterface $start;

    protected DateTimeInterface $end;

    public function __construct(string $statistic)
    {
        $this->statistic = new $statistic();

        $this->period = 'week';

        $this->start = now()->subMonth();

        $this->end = now();
    }

    public static function for(string $statistic): self
    {
        return new self($statistic);
    }

    public function groupByYear(): self
    {
        $this->period = 'year';

        return $this;
    }

    public function groupByMonth(): self
    {
        $this->period = 'month';

        return $this;
    }

    public function groupByWeek(): self
    {
        $this->period = 'week';

        return $this;
    }

    public function groupByDay(): self
    {
        $this->period = 'day';

        return $this;
    }

    public function groupByHour(): self
    {
        $this->period = 'hour';

        return $this;
    }

    public function start(DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function end(DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function get(): Collection
    {
        $periods = $this->generatePeriods();

        $lastPeriodValue = $this->getValue($this->start);

        $changes = $this->queryStats()
            ->whereType(StatsEvent::TYPE_CHANGE)
            ->where('created_at', '>=', $this->start)
            ->where('created_at', '<', $this->end)
            ->get();

        $differencesPerPeriod = $this->getDifferencesPerPeriod();

        // TODO: Fetch all latest sets per period in advance
        // DB::select('WITH ranked_teams AS (
        //   SELECT ROW_NUMBER() OVER (PARTITION BY billing_country ORDER BY id DESC) AS rn, teams.*
        //   FROM teams
        // )
        // SELECT ranked_teams.billing_country FROM ranked_teams WHERE rn = 1');

        return $periods->map(function (array $periodBoundaries) use ($changes, $differencesPerPeriod, &$lastPeriodValue) {
            [$periodStart, $periodEnd, $periodKey] = $periodBoundaries;

            $setEvent = $this->queryStats()
                ->whereType(StatsEvent::TYPE_SET)
                ->where('created_at', '>=', $periodStart)
                ->where('created_at', '<', $periodEnd)
                ->latest()->first();

            $startValue = $setEvent['value'] ?? $lastPeriodValue;
            $applyChangesAfter = $setEvent['created_at'] ?? $periodStart;

            $difference = $changes
                ->where('created_at', '>=', $applyChangesAfter)
                ->where('created_at', '<', $periodEnd)
                ->sum('value');

            $value = $startValue + $difference;
            $lastPeriodValue = $value;

            return [
                'start' => $periodStart,
                'end' => $periodEnd,
                'value' => $value,
                'increments' => $differencesPerPeriod[$periodKey]['increments'] ?? 0,
                'decrements' => $differencesPerPeriod[$periodKey]['decrements'] ?? 0,
                'difference' => $differencesPerPeriod[$periodKey]['difference'] ?? 0,
            ];
        });
    }

    /**
     * Gets the value at a point in time by using the previous
     * snapshot and the changes since that snapshot.
     */
    public function getValue(DateTimeInterface $dateTime): int
    {
        $nearestSet = $this->queryStats()
            ->where('type', StatsEvent::TYPE_SET)
            ->where('created_at', '<', $dateTime)
            ->orderByDesc('created_at')
            ->first();

        $startId = optional($nearestSet)->id ?? 0;
        $startValue = optional($nearestSet)->value ?? 0;

        $differenceSinceSet = $this->queryStats()
            ->where('type', StatsEvent::TYPE_CHANGE)
            ->where('id', '>', $startId)
            ->where('created_at', '<', $dateTime)
            ->sum('value');

        return $startValue + $differenceSinceSet;
    }

    protected function queryStats(): Builder
    {
        return StatsEvent::query()
            ->where('name', $this->statistic->getName());
    }

    protected function querySnapshots(): Builder
    {
        return StatsSnapshot::query()
            ->where('name', $this->statistic->getName());
    }

    public function generatePeriods(): Collection
    {
        $data = collect();
        $currentDateTime = new Carbon($this->start);

        do {
            $data->push([
                $currentDateTime->copy(),
                $currentDateTime->copy()->add(1, $this->period),
                $currentDateTime->format($this->getPeriodTimestampFormat()),
            ]);

            $currentDateTime->add(1, $this->period);
        } while ($currentDateTime->lt($this->end));

        return $data;
    }

    protected function getDifferencesPerPeriod()
    {
        return $this->queryStats()
            ->whereType(StatsEvent::TYPE_CHANGE)
            ->where('created_at', '>=', $this->start)
            ->where('created_at', '<', $this->end)
            ->selectRaw('sum(case when value > 0 then value else 0 end) as increments')
            ->selectRaw('abs(sum(case when value < 0 then value else 0 end)) as decrements')
            ->selectRaw('sum(value) as difference')
            ->groupByPeriod($this->period)
            ->get()
            ->keyBy('period');
    }

    public function getPeriodTimestampFormat(): string
    {
        return match($this->period) {
            'year' => 'Y',
            'month' => 'Y-m',
            'week' => 'oW', // see https://stackoverflow.com/questions/15562270/php-datew-vs-mysql-yearweeknow
            'day' => 'Y-m-d',
            'hour' => 'Y-m-d H',
            'minute' => 'Y-m-d H:i',
        };
    }
}
