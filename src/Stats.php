<?php

namespace Spatie\Stats;

use Carbon\Carbon;
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

        // get latest snapshot per period
        // use sum of changes for periods without snapshots

        $changes = $this->queryStats()
            ->changes()
            ->where('timestamp', '>', $this->start)
            ->get();

        // YEARWEEK(NOW(), 3) mode 3 is the same as PHP's `oW` format
        $snapshotsPerGroup = $this->queryStats()
            ->snapshots()
            ->selectRaw('YEARWEEK(timestamp, 3) AS week')
            ->selectRaw('SUM(value) AS difference')
            ->where('timestamp', '>', $this->start)
            ->groupByRaw('YEARWEEK(timestamp, 3)')
            ->get();

        $periods = collect($this->generatePeriods());

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

    protected function generatePeriods(): array
    {
        $data = [];
        $currentDateTime = new Carbon($this->start);

        do {
            $data[] = $currentDateTime->format('oW'); // week format
            $currentDateTime->addWeek();
        } while($currentDateTime->lte($this->end));

        return $data;
    }
}
