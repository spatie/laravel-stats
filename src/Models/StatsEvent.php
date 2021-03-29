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

    public function scopeIncrements(Builder $query)
    {
        $query->where('value', '>', 0);
    }

    public function scopeDecrements(Builder $query)
    {
        $query->where('value', '<', 0);
    }
}
