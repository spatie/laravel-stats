<?php

namespace Spatie\Stats;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
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

    public function get(): array
    {
        // get initial value
        // get changes per week
        // apply changes to initial value per week


        // events W1
        // increase 100
        // increase 51
        // snapshot value=151 increase=151 dec=0
        //
        // decr 150
        // increase 100
        // increase 51

        // output
        // value 151
        // incr 151
        // decr 0
        //
        // value



        $initialValue = $this->getValue($this->start);


        // get latest snapshot per period
        // use sum of changes for periods without snapshots

        $changes = $this->queryStats()
            ->where('created_at', '>', $this->start)
            ->get();

        // YEARWEEK(NOW(), 3) mode 3 is the same as PHP's `oW` format
        $snapshotsPerGroup = $this->queryStats()
            ->snapshots()
            ->selectRaw('YEARWEEK(created_at, 3) AS week')
            ->selectRaw('SUM(value) AS difference')
            ->where('created_at', '>', $this->start)
            ->groupByRaw('YEARWEEK(created_at, 3)')
            ->get();

        $periods = collect($this->generatePeriods());

        return 0;

//        return $differencePerGroup->reduce(fn (int $previousValue, StatsEvent $statisticEvent) => [
//            'difference' => $statisticEvent->difference,
//            'current' => $previousValue + $statisticEvent->difference,
//            'week' => $statisticEvent->week,
//        ], $initialValue);
    }

    public function getValue(DateTimeInterface $dateTime): int
    {
        /**
         * Gets the value at a point in time by using the previous
         * snapshot and the changes since that snapshot.
         */

        $nearestSet = $this->queryStats()
            ->whereType(StatsEvent::TYPE_SET)
            ->where('created_at', '<', $dateTime)
            ->orderByDesc('created_at')
            ->first();

        $startId = optional($nearestSet)->id ?? 0;
        $startValue = optional($nearestSet)->value ?? 0;

        $differenceSinceSet = $this->queryStats()
            ->whereType(StatsEvent::TYPE_CHANGE)
            ->where('id', '>', $startId)
            ->where('created_at', '<=', $dateTime)
            ->sum('value');

        return $startValue + $differenceSinceSet;
    }

    protected function queryStats(): Builder
    {
        return StatsEvent::query()
            ->where('statistic', $this->statistic->getKey());
    }

    protected function querySnapshots(): Builder
    {
        return StatsSnapshot::query()
            ->where('statistic', $this->statistic->getKey());
    }

    protected function generatePeriods(): array
    {
        $data = [];
        $currentDateTime = new Carbon($this->start);

        do {
            $data[] = $currentDateTime->format('oW'); // week format
            $currentDateTime->addWeek();
        } while ($currentDateTime->lte($this->end));

        return $data;
    }
}
