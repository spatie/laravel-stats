<?php

namespace Spatie\Stats;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\Stats\Models\StatsEvent;

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

        $changes = $this->queryStats()
            ->whereType(StatsEvent::TYPE_CHANGE)
            ->where('created_at', '>=', $this->start)
            ->where('created_at', '<', $this->end)
            ->get();

        $differencesPerPeriod = $this->getDifferencesPerPeriod();

        $latestSetPerPeriod = $this->getLatestSetPerPeriod();

        $lastPeriodValue = $this->getValue($this->start);

        return $periods->map(function (array $periodBoundaries) use ($latestSetPerPeriod, $changes, $differencesPerPeriod, &$lastPeriodValue) {
            [$periodStart, $periodEnd, $periodKey] = $periodBoundaries;

            $setEvent = $latestSetPerPeriod->where('period', $periodKey)->first();

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
                'value' => (int) $value,
                'increments' => (int) ($differencesPerPeriod[$periodKey]['increments'] ?? 0),
                'decrements' => (int) ($differencesPerPeriod[$periodKey]['decrements'] ?? 0),
                'difference' => (int) ($differencesPerPeriod[$periodKey]['difference'] ?? 0),
            ];
        });
    }

    /**
     * Gets the value at a point in time by using the previous
     * snapshot and the changes since that snapshot.
     *
     * @param \DateTimeInterface $dateTime
     *
     * @return int
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

    public function generatePeriods(): Collection
    {
        $data = collect();
        $currentDateTime = (new Carbon($this->start))->startOf($this->period);

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

    protected function queryStats(): Builder
    {
        return StatsEvent::query()
            ->where('name', $this->statistic->getName());
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

    protected function getLatestSetPerPeriod()
    {
        $periodDateFormat = StatsEvent::getPeriodDateFormat($this->period);

        $rankedSets = $this->queryStats()
            ->selectRaw("ROW_NUMBER() OVER (PARTITION BY {$periodDateFormat} ORDER BY `id` DESC) AS rn, `stats_events`.*, {$periodDateFormat} as period")
            ->whereType(StatsEvent::TYPE_SET)
            ->where('created_at', '>=', $this->start)
            ->where('created_at', '<', $this->end)
            ->get();

        $latestSetPerPeriod = $rankedSets->where('rn', 1);

        return $latestSetPerPeriod;
    }
}
