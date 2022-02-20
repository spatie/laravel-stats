<?php

namespace Spatie\Stats\Traits;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Stats\StatsQuery;

trait HasStats
{

    public function scopeGroupByPeriod(Builder $query, string $period): void
    {
        $periodGroupBy = StatsQuery::getPeriodDateFormat($period);
        $query->groupByRaw($periodGroupBy)->selectRaw("{$periodGroupBy} as period");
    }

    public function scopeIncrements(Builder $query): void
    {
        $query->where('value', '>', 0);
    }

    public function scopeDecrements(Builder $query): void
    {
        $query->where('value', '<', 0);
    }
}
