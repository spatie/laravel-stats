<?php

namespace Spatie\Stats\Tests;

use Carbon\Carbon;
use Spatie\Stats\Stats;
use Spatie\Stats\Tests\Stats\OrderStats;

class StatsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2020-01-01');
    }

    /** @test */
    public function it_can_get_the_value_at_a_given_time()
    {
        (new OrderStats())->set(3, now()->subDays(19));
        (new OrderStats())->decrease(1, now()->subDays(4));
        (new OrderStats())->increase(3, now()->subDays(2));

        $this->assertEquals(0, Stats::for(OrderStats::class)->getValue(now()->subDays(30)));
        $this->assertEquals(3, Stats::for(OrderStats::class)->getValue(now()->subDays(18)));
        $this->assertEquals(5, Stats::for(OrderStats::class)->getValue(now()));
    }

    /** @test */
    public function it_can_get_stats()
    {
        (new OrderStats())->set(3, now()->subMonth());
        (new OrderStats())->decrease(1, now()->subDays(13));
        (new OrderStats())->increase(3, now()->subDays(12));
        (new OrderStats())->set(3, now()->subDays(6));
        (new OrderStats())->decrease(1, now()->subDays(5));
        (new OrderStats())->increase(3, now()->subDays(4));

        $stats = Stats::for(OrderStats::class)
            ->start(now()->subWeeks(2))
            ->end(now())
            ->groupByWeek()
            ->get();

        $expected = [
                [
                    'orders' => [
                        'current' => 5,
                        'additions' => +3,
                        'subtractions' => -1,
                        'difference' => 2,
                    ],
                    'datetime' => now()->subWeeks(2),
                ],
                [
                    'orders' => [
                        'current' => 5,
                        'additions' => +3,
                        'subtractions' => -1,
                        'difference' => 2,
                    ],
                    'datetime' => now()->subWeeks(1),
                ],
            ];

        $this->assertEquals($expected, $stats);
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
