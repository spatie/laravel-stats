<?php

namespace Spatie\Statistics\Commands;

use Illuminate\Console\Command;

class StatisticsCommand extends Command
{
    public $signature = 'laravel-statistics';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
