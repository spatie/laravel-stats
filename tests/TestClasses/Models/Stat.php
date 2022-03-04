<?php

namespace Spatie\Stats\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Stats\Models\StatsEvent;

class Stat extends Model
{

    public function events()
    {
        return $this->hasMany(StatsEvent::class);
    }
}
