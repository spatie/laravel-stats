<?php

namespace Spatie\Stats;

use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class StatsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('stats')
            ->hasConfigFile()
            ->hasMigration('create_stats_tables');
    }

    public function boot(): void
    {
        $this->listenForConfiguredEvents();
    }

    protected function listenForConfiguredEvents(): void
    {
        foreach (config('stats.events') as $event => $statistic) {
            Event::listen($event, $statistic);
        }
    }
}
