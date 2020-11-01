<?php

namespace Spatie\LaravelStatistics\Commands;

use Illuminate\Console\Command;

class LaravelStatisticsCommand extends Command
{
    public $signature = 'laravel-statistics';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
