<?php

namespace Spatie\Stats\Models;

use Illuminate\Database\Eloquent\Model;

class StatsSnapshot extends Model
{
    protected $casts = [
        'value' => 'integer',
        'increase' => 'integer',
        'decrease' => 'integer',
    ];

    protected $guarded = [];
}
