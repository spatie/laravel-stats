<?php

namespace Spatie\Stats\Tests;

use Spatie\Stats\Stats;
use Spatie\Stats\Tests\Stats\OrderStats;

class StatsTest extends TestCase
{
    /** @test */
    public function it_can_get_stats()
    {
        (new OrderStats())->increase(10, now()->subDays(40));
        (new OrderStats())->decrease(2, now()->subDays(33));
        (new OrderStats())->decrease(1, now()->subDays(24));
        (new OrderStats())->increase(3, now()->subDays(20));
        (new OrderStats())->set(3, now()->subDays(19));
        (new OrderStats())->decrease(1, now()->subDays(4));
        (new OrderStats())->increase(3, now()->subDays(2));

        $a = Stats::for(OrderStats::class)->start(now()->subWeek())->get();
    }

    /** @test */
    public function it_can_get_stats_2()
    {
        (new OrderStats())->increase(10, now()->subWeeks(14));
        (new OrderStats())->decrease(2, now()->subWeeks(12));
        (new OrderStats())->decrease(1, now()->subWeeks(10));
        (new OrderStats())->increase(3, now()->subWeeks(8));
        (new OrderStats())->set(3, now()->subWeeks(6));
        (new OrderStats())->decrease(1, now()->subWeeks(4));
        (new OrderStats())->increase(3, now()->subWeeks(2));

        $a = Stats::for(OrderStats::class)->start(now()->subWeek())->get();
    }
}
