<?php

namespace Spatie\Stats\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StatsEvent extends Model
{
    const TYPE_SET = 'set';
    const TYPE_CHANGE = 'change';

    protected $casts = [
        'value' => 'integer',
    ];

    protected $guarded = [];

    public function scopeGroupByPeriod(Builder $query, string $period)
    {
        $periodGroupBy = match($period) {
            'year' => "date_format(created_at,'%Y')",
            'month' => "date_format(created_at,'%Y-%m')",
            'week' => "yearweek(created_at, 3)", // see https://stackoverflow.com/questions/15562270/php-datew-vs-mysql-yearweeknow
            'day' => "date_format(created_at,'%Y-%m-%d')",
            'hour' => "date_format(created_at,'%Y-%m-%d %H')",
            'minute' => "date_format(created_at,'%Y-%m-%d %H:%i')",
        };

        $query->groupByRaw($periodGroupBy)->selectRaw("{$periodGroupBy} as period");
    }

    public function scopeIncrements(Builder $query)
    {
        $query->where('value', '>', 0);
    }

    public function scopeDecrements(Builder $query)
    {
        $query->where('value', '<', 0);
    }
}
