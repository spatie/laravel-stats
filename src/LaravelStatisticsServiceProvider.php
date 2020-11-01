<?php

namespace Spatie\LaravelStatistics;

use Illuminate\Support\ServiceProvider;
use Spatie\LaravelStatistics\Commands\LaravelStatisticsCommand;

class LaravelStatisticsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-statistics.php' => config_path('laravel-statistics.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/laravel-statistics'),
            ], 'views');

            $migrationFileName = 'create_laravel_statistics_table.php';
            if (! $this->migrationFileExists($migrationFileName)) {
                $this->publishes([
                    __DIR__ . "/../database/migrations/{$migrationFileName}.stub" => database_path('migrations/' . date('Y_m_d_His', time()) . '_' . $migrationFileName),
                ], 'migrations');
            }

            $this->commands([
                LaravelStatisticsCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-statistics');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-statistics.php', 'laravel-statistics');
    }

    public static function migrationFileExists(string $migrationFileName): bool
    {
        $len = strlen($migrationFileName);
        foreach (glob(database_path("migrations/*.php")) as $filename) {
            if ((substr($filename, -$len) === $migrationFileName)) {
                return true;
            }
        }

        return false;
    }
}
