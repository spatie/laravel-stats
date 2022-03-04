<?php

namespace Spatie\Stats\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Stats\DataPoint;
use Spatie\Stats\Traits\HasStats;

class StatsEvent extends Model
{
    use HasStats;

    /**
     * @deprecated use DataPoint::TYPE_SET
     */
    const TYPE_SET = DataPoint::TYPE_SET;

    /**
     * @deprecated use DataPoint::TYPE_CHANGE
     */
    const TYPE_CHANGE = DataPoint::TYPE_CHANGE;

    protected $casts = [
        'value' => 'integer',
    ];

    protected $guarded = [];
}
