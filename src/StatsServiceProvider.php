<?php

namespace Spatie\Stats;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\Stats\Commands\StatsCommand;

class StatsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('stats')
            ->hasConfigFile()
            ->hasMigration('create_stats_events_table')
            ->hasCommand(StatsCommand::class);
    }
}
