<?php

namespace Spatie\Stats\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasStats
{

    public function scopeGroupByPeriod(Builder $query, string $period): void
    {
        $periodGroupBy = static::getPeriodDateFormat($period);
        $query->groupByRaw($periodGroupBy)->selectRaw("{$periodGroupBy} as period");
    }

    public static function getPeriodDateFormat(string $period): string
    {
        return match ($period) {
            'year' => "date_format(created_at,'%Y')",
            'month' => "date_format(created_at,'%Y-%m')",
            'week' => "yearweek(created_at, 3)", // see https://stackoverflow.com/questions/15562270/php-datew-vs-mysql-yearweeknow
            'day' => "date_format(created_at,'%Y-%m-%d')",
            'hour' => "date_format(created_at,'%Y-%m-%d %H')",
            'minute' => "date_format(created_at,'%Y-%m-%d %H:%i')",
        };
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
