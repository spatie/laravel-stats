<?php

namespace Spatie\Stats\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Stats\Traits\HasStats;

class StatsEvent extends Model
{
    use HasStats;

    const TYPE_SET = 'set';
    const TYPE_CHANGE = 'change';

    protected $casts = [
        'value' => 'integer',
    ];

    protected $guarded = [];
}
