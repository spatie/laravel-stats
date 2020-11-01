<?php

namespace Spatie\Statistics\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StatisticEvent extends Model
{
    protected $casts = [
        'value' => 'integer',
    ];

    protected $guarded = [];

    public function scopeChanges(Builder $query)
    {
        $query->where('type', 'change');
    }

    public function scopeIncrements(Builder $query)
    {
        $query->where('type', 'change')->where('value', '>', 0);
    }

    public function scopeDecrements(Builder $query)
    {
        $query->where('type', 'change')->where('value', '<', 0);
    }

    public function scopeSnapshots(Builder $query)
    {
        $query->where('type', 'snapshot');
    }
}
