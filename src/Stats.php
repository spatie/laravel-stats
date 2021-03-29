<?php

namespace Spatie\Stats;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Stats\Models\StatsEvent;

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

    public function groupByWeek(): self
    {
        $this->grouping = 'week';

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

    public function get(): array | int
    {
        $initialValue = $this->getValue($this->start);

        $differencePerGroup = $this->queryStats()
            ->changes()
            ->selectRaw('YEARWEEK(timestamp) AS week')
            ->selectRaw('SUM(value) AS difference')
            ->where('timestamp', '>', $this->start)
            ->groupByRaw('YEARWEEK(timestamp)')
            ->get();

//        if ($differencePerGroup->isEmpty()) {
//            return;
//        }

        return $differencePerGroup->reduce(fn (int $previousValue, StatsEvent $statisticEvent) => [
            'difference' => $statisticEvent->difference,
            'current' => $previousValue + $statisticEvent->difference,
            'week' => $statisticEvent->week,
        ], $initialValue);
    }

    protected function getValue(DateTimeInterface $dateTime): int
    {
        /**
         * Gets the value at a point in time by using the previous
         * snapshot and the changes since that snapshot.
         */

        $nearestSnapshot = $this->queryStats()
            ->where('type', 'snapshot')
            ->where('timestamp', '<', $dateTime)
            ->orderByDesc('timestamp')
            ->first();

        $startTimestamp = optional($nearestSnapshot)->timestamp ?? 0;
        $startValue = optional($nearestSnapshot)->value ?? 0;

        return $startValue + $this->queryStats()
            ->changes()
            ->where('timestamp', '>', $startTimestamp)
            ->sum('value');
    }

    protected function queryStats(): Builder
    {
        return StatsEvent::query()
            ->where('statistic', $this->statistic->getKey());
    }
}
