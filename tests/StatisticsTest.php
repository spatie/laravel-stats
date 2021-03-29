<?php

namespace Spatie\Stats\Tests;

use Spatie\Stats\Stats;
use Spatie\Stats\Tests\Statistics\OrderBaseStats;

class StatisticsTest extends TestCase
{
    /** @test */
    public function it_can_get_stats()
    {
        (new OrderBaseStats())->increase(10, now()->subDays(14));
        (new OrderBaseStats())->decrease(2, now()->subDays(12));
        (new OrderBaseStats())->decrease(1, now()->subDays(10));
        (new OrderBaseStats())->increase(3, now()->subDays(8));
        (new OrderBaseStats())->set(3, now()->subDays(6));
        (new OrderBaseStats())->decrease(1, now()->subDays(4));
        (new OrderBaseStats())->increase(3, now()->subDays(2));

        $a = Stats::for(OrderBaseStats::class)->start(now()->subWeek())->get();
    }

    /** @test */
    public function it_can_get_stats_2()
    {
        (new OrderBaseStats())->increase(10, now()->subWeeks(14));
        (new OrderBaseStats())->decrease(2, now()->subWeeks(12));
        (new OrderBaseStats())->decrease(1, now()->subWeeks(10));
        (new OrderBaseStats())->increase(3, now()->subWeeks(8));
        (new OrderBaseStats())->set(3, now()->subWeeks(6));
        (new OrderBaseStats())->decrease(1, now()->subWeeks(4));
        (new OrderBaseStats())->increase(3, now()->subWeeks(2));

        $a = Stats::for(OrderBaseStats::class)->start(now()->subWeek())->get();
    }
}
