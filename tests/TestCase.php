<?php

namespace Spatie\Statistics\Tests;

use CreateStatisticEventsTable;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Statistics\StatisticsServiceProvider;

class TestCase extends Orchestra
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            StatisticsServiceProvider::class,
        ];
    }

    public function setupDatabase()
    {
        include_once __DIR__.'/../database/migrations/create_statistic_events_table.php.stub';

        (new CreateStatisticEventsTable())->up();
    }
}
