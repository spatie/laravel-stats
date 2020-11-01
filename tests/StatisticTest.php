<?php

namespace Spatie\Statistics\Tests;

use Spatie\Statistics\Tests\Statistics\OrderStats;

class StatisticTest extends TestCase
{
    /** @test */
    public function it_can_create_events_for_increments()
    {
        $stats = new OrderStats();

        $stats->increase(1);

        $this->assertDatabaseHas('statistic_events', [
            'statistic' => 'OrderStats',
            'value' => 1,
            'type' => 'change',
        ]);
    }
}
