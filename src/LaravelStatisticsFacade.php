<?php

namespace Spatie\LaravelStatistics;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spatie\LaravelStatistics\LaravelStatistics
 */
class LaravelStatisticsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-statistics';
    }
}
