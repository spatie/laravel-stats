<?php

namespace Spatie\Stats;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\Stats\Console\StatsMakeCommand;

class StatsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('stats')
            ->hasMigration('create_stats_tables')
            ->hasCommand(StatsMakeCommand::class);
    }
}
