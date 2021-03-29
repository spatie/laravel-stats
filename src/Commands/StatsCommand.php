<?php

namespace Spatie\Stats\Commands;

use Illuminate\Console\Command;

class StatsCommand extends Command
{
    public $signature = 'laravel-stats';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
