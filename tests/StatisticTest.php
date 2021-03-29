<?php

namespace Spatie\Stats\Tests;

use Spatie\Stats\Tests\Stats\OrderStats;

class StatisticTest extends TestCase
{
    /** @test */
    public function it_can_create_events_for_increments()
    {
        $stats = new OrderStats();

        $stats->increase(1);

        $this->assertDatabaseHas('stats_events', [
            'name' => 'OrderStats',
            'value' => 1,
            'type' => 'change',
        ]);
    }
}
