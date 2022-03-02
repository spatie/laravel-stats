<?php

namespace Spatie\Stats\Tests;

use CreateStatsTables;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Stats\StatsServiceProvider;

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
            StatsServiceProvider::class,
        ];
    }

    public function setupDatabase()
    {
        include_once __DIR__.'/../database/migrations/create_stats_tables.php.stub';

        $this->app['db']->connection()->getSchemaBuilder()->create('stats', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        (new CreateStatsTables())->up();

        $this->app['db']->connection()->getSchemaBuilder()->table('stats_events', function (Blueprint $table) {
            $table->string('stat_id')->nullable()->after('id');
            $table->string('name')->nullable()->change();
        });
    }
}
