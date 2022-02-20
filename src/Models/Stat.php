<?php

namespace Spatie\Stats\Models;

use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{

    public function events()
    {
        return $this->hasMany(StatsEvent::class);
    }
}
