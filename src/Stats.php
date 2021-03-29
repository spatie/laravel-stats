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

        return $periods->map(function (array $periodBoundaries) use (&$lastPeriodValue) {
            [$periodStart, $periodEnd] = $periodBoundaries;

            $setEvent = $this->queryStats()
                ->whereType(StatsEvent::TYPE_SET)
                ->where('created_at', '>=', $periodStart)
                ->where('created_at', '<', $periodEnd)
                ->latest()->first();

            $startValue = $setEvent['value'] ?? $lastPeriodValue;
            $applyChangesAfter = $setEvent['created_at'] ?? $periodStart;

            $difference = $this->queryStats()
                ->whereType(StatsEvent::TYPE_CHANGE)
                ->where('created_at', '>=', $applyChangesAfter)
                ->where('created_at', '<', $periodEnd)
                ->sum('value');

            $value = $startValue + $difference;
            $lastPeriodValue = $value;

            $increments = (int) $this->queryStats()
                ->increments()
                ->whereType(StatsEvent::TYPE_CHANGE)
                ->where('created_at', '>=', $periodStart)
                ->where('created_at', '<', $periodEnd)
                ->sum('value');

            $decrements = (int) $this->queryStats()
                ->decrements()
                ->whereType(StatsEvent::TYPE_CHANGE)
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
            ->where('created_at', '<', $dateTime)
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
