<?php

namespace Spatie\Statistics\Tests;

use Spatie\Statistics\Statistics;
use Spatie\Statistics\Tests\Statistics\OrderStats;

class StatisticsTest extends TestCase
{
    /** @test */
    public function it_can_get_stats()
    {
        (new OrderStats())->increase(10, now()->subDays(14));
        (new OrderStats())->decrease(2, now()->subDays(12));
        (new OrderStats())->decrease(1, now()->subDays(10));
        (new OrderStats())->increase(3, now()->subDays(8));
        (new OrderStats())->set(3, now()->subDays(6));
        (new OrderStats())->decrease(1, now()->subDays(4));
        (new OrderStats())->increase(3, now()->subDays(2));

        $a = Statistics::for(OrderStats::class)->start(now()->subWeek())->get();
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

        $a = Statistics::for(OrderStats::class)->start(now()->subWeek())->get();
    }
}
