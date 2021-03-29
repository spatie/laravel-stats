<?php

namespace Spatie\Stats\Tests;

use Spatie\Stats\Tests\Statistics\OrderBaseStats;

class StatisticTest extends TestCase
{
    /** @test */
    public function it_can_create_events_for_increments()
    {
        $stats = new OrderBaseStats();

        $stats->increase(1);

        $this->assertDatabaseHas('stats_events', [
            'statistic' => 'OrderBaseStats',
            'value' => 1,
            'type' => 'change',
        ]);
    }
}
