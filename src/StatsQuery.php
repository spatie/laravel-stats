<?php

namespace Spatie\Stats;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class StatsQuery
{
    private Model|Relation|string $subject;

    private array $attributes = [];

    protected string $period;

    protected DateTimeInterface $start;

    protected DateTimeInterface $end;

    public function __construct(Model|Relation|string $subject, array $attributes = [])
    {
        $this->subject = $subject;

        $this->attributes = $attributes;

        $this->period = 'week';

        $this->start = now()->subMonth();

        $this->end = now();
    }

    public static function for(Model|Relation|string $subject, array $attributes = []): self
    {
        return new self($subject, $attributes);
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

    /** @return Collection|DataPoint[] */
    public function get(): Collection
    {
        $periods = $this->generatePeriods();

        $differencesPerPeriod = $this->getDifferencesPerPeriod();

        $latestSetPerPeriod = $this->getLatestSetPerPeriod();

        $lastPeriodValue = $this->getValue($this->start);

        return $periods->map(function (array $periodBoundaries) use ($latestSetPerPeriod, $differencesPerPeriod, &$lastPeriodValue) {
            [$periodStart, $periodEnd, $periodKey] = $periodBoundaries;

            $setEvent = $latestSetPerPeriod->where('period', $periodKey)->first();

            $startValue = $setEvent['value'] ?? $lastPeriodValue;

            $applyChangesAfter = $setEvent['created_at'] ?? $periodStart;

            $difference = $this->queryStats()
                ->where('type', DataPoint::TYPE_CHANGE)
                ->where('created_at', '>=', $applyChangesAfter)
                ->where('created_at', '<', $periodEnd)
                ->sum('value');

            $value = $startValue + $difference;
            $lastPeriodValue = $value;

            return new DataPoint(
                start: $periodStart,
                end: $periodEnd,
                value: (int) $value,
                increments: (int) ($differencesPerPeriod[$periodKey]['increments'] ?? 0),
                decrements: (int) ($differencesPerPeriod[$periodKey]['decrements'] ?? 0),
                difference: (int) ($differencesPerPeriod[$periodKey]['difference'] ?? 0),
            );
        });
    }

    /**
     * Gets the value at a point in time by using the previous
     * snapshot and the changes since that snapshot.
     *
     * @param DateTimeInterface $dateTime
     *
     * @return int
     */
    public function getValue(DateTimeInterface $dateTime): int
    {
        $nearestSet = $this->queryStats()
            ->where('type', DataPoint::TYPE_SET)
            ->where('created_at', '<', $dateTime)
            ->orderByDesc('created_at')
            ->first();

        $startId = optional($nearestSet)->id ?? 0;
        $startValue = optional($nearestSet)->value ?? 0;

        $differenceSinceSet = $this->queryStats()
            ->where('type', DataPoint::TYPE_CHANGE)
            ->where($this->getStatsKey(), '>', $startId)
            ->where('created_at', '<', $dateTime)
            ->sum('value');

        return $startValue + $differenceSinceSet;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public static function getPeriodDateFormat(string $period): string
    {
        $dbDriver = Config::get('database.connections.'.Config::get('database.default', 'mysql').'.driver', 'mysql');

        if ($dbDriver === 'pgsql') {
            return match ($period) {
                'year' => "to_char(created_at, 'YYYY')",
                'month' => "to_char(created_at, 'YYYY-MM')",
                'week' => "to_char(created_at, 'IYYYIW')",
                'day' => "to_char(created_at, 'YYYY-MM-DD')",
                'hour' => "to_char(created_at, 'YYYY-MM-DD HH24')",
                'minute' => "to_char(created_at, 'YYYY-MM-DD HH24:MI')",
            };
        }

        return match ($period) {
            'year' => "date_format(created_at,'%Y')",
            'month' => "date_format(created_at,'%Y-%m')",
            'week' => $dbDriver === 'mysql' ? "yearweek(created_at, 3)" : "strftime('%Y%W')", // see https://stackoverflow.com/questions/15562270/php-datew-vs-mysql-yearweeknow
            'day' => "date_format(created_at,'%Y-%m-%d')",
            'hour' => "date_format(created_at,'%Y-%m-%d %H')",
            'minute' => "date_format(created_at,'%Y-%m-%d %H:%i')",
        };
    }

    protected function generatePeriods(): Collection
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

    protected function getPeriodTimestampFormat(): string
    {
        return match ($this->period) {
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
        if ($this->subject instanceof Relation) {
            return $this->subject->getQuery()->clone()->where($this->attributes);
        }

        /** @var Model $subject */
        $subject = $this->subject;
        if (is_string($subject) && class_exists($subject)) {
            $subject = new $subject;
        }

        return $subject->newQuery()->where($this->attributes);
    }

    protected function getDifferencesPerPeriod(): EloquentCollection
    {
        return $this->queryStats()
            ->where('type', DataPoint::TYPE_CHANGE)
            ->where('created_at', '>=', $this->start)
            ->where('created_at', '<', $this->end)
            ->selectRaw('sum(case when value > 0 then value else 0 end) as increments')
            ->selectRaw('abs(sum(case when value < 0 then value else 0 end)) as decrements')
            ->selectRaw('sum(value) as difference')
            ->groupByPeriod($this->period)
            ->get()
            ->keyBy('period');
    }

    protected function getLatestSetPerPeriod(): EloquentCollection
    {
        $periodDateFormat = static::getPeriodDateFormat($this->period);

        $query = $this->queryStats();

        $statsTable = $query->getGrammar()->wrap($this->getStatsTableName());
        $statsKey = $query->getGrammar()->wrap($this->getStatsKey());

        $rankedSets = $query
            ->selectRaw("ROW_NUMBER() OVER (PARTITION BY {$periodDateFormat} ORDER BY {$statsKey} DESC) AS rn, {$statsTable}.*, {$periodDateFormat} as period")
            ->where('type', DataPoint::TYPE_SET)
            ->where('created_at', '>=', $this->start)
            ->where('created_at', '<', $this->end)
            ->get();

        $latestSetPerPeriod = $rankedSets->where('rn', 1);

        return $latestSetPerPeriod;
    }

    protected function getStatsKey(): string
    {
        if ($this->subject instanceof Relation) {
            return $this->subject->getRelated()->getKeyName();
        }

        /** @var Model $subject */
        $subject = $this->subject;
        if (is_string($subject) && class_exists($subject)) {
            $subject = new $subject;
        }

        return $subject->getKeyName();
    }

    protected function getStatsTableName(): string
    {
        if ($this->subject instanceof Relation) {
            return $this->subject->getQuery()->getGrammar()->getTablePrefix().$this->subject->getRelated()->getTable();
        }

        /** @var Model $subject */
        $subject = $this->subject;
        if (is_string($subject) && class_exists($subject)) {
            $subject = new $subject;
        }

        return $subject->getQuery()->getGrammar()->getTablePrefix().$subject->getTable();
    }
}
