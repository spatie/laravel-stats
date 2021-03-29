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

    protected string $grouping;

    protected DateTimeInterface $start;

    protected DateTimeInterface $end;

    public function __construct(string $statistic)
    {
        $this->statistic = new $statistic();

        $this->grouping = 'week';

        $this->start = now()->subMonth();

        $this->end = now();
    }

    public static function for(string $statistic): self
    {
        return new self($statistic);
    }

    public function groupByMonth(): self
    {
        $this->grouping = 'month';

        return $this;
    }

    public function groupByWeek(): self
    {
        $this->grouping = 'week';

        return $this;
    }

    public function groupByDay(): self
    {
        $this->grouping = 'day';

        return $this;
    }

    public function groupByHour(): self
    {
        $this->grouping = 'hour';

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

        // TODO: Fetch all incr/decr in advance
//        $changes = $this->queryStats()
//            ->whereType(StatsEvent::TYPE_CHANGE)
//            ->where('created_at', '>=', $this->start)
//            ->where('created_at', '<', $this->end)
//            ->selectRaw('sum(values) as difference, sum(values > 0) as increments, sum(values < 0) as decrement')
//            ->groupByPeriod()
//            ->get();

        // TODO: Fetch all latest sets per period in advance
        // DB::select('WITH ranked_teams AS (
        //   SELECT ROW_NUMBER() OVER (PARTITION BY billing_country ORDER BY id DESC) AS rn, teams.*
        //   FROM teams
        // )
        // SELECT ranked_teams.billing_country FROM ranked_teams WHERE rn = 1');

        return $periods->map(function (array $periodBoundaries) use ($changes, &$lastPeriodValue) {
            [$periodStart, $periodEnd] = $periodBoundaries;

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

            $increments = (int) $changes->where('value', '>', 0)
                ->where('created_at', '>=', $periodStart)
                ->where('created_at', '<', $periodEnd)
                ->sum('value');

            $decrements = (int) $changes->where('value', '<', 0)
                ->where('created_at', '>=', $periodStart)
                ->where('created_at', '<', $periodEnd)
                ->sum('value');
            $decrements = abs($decrements);

            return [
                'start' => $periodStart,
                'end' => $periodEnd,
                'value' => $value,
                'increments' => $increments,
                'decrements' => $decrements,
                'difference' => $increments - $decrements,
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
            ->where('name', $this->statistic->getKey());
    }

    protected function querySnapshots(): Builder
    {
        return StatsSnapshot::query()
            ->where('name', $this->statistic->getKey());
    }

    public function generatePeriods(): Collection
    {
        $data = collect();
        $currentDateTime = new Carbon($this->start);

        do {
            $data->push([
                $currentDateTime->copy(),
                $currentDateTime->copy()->add(1, $this->grouping),
            ]);
            $currentDateTime->add(1, $this->grouping);
        } while ($currentDateTime->lt($this->end));

        return $data;
    }
}
