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
    public function it_can_generate_and_array_of_periods()
    {
        $periods = Stats::for(OrderStats::class)->start(now()->subYear())->end(now())->generatePeriods();

        $this->assertCount(53, $periods);

        $this->assertEquals([
            'start' => Carbon::parse('2019-01-01'),
            'end' => Carbon::parse('2019-01-08'),
        ], $periods[0]);

        $this->assertEquals([
            'start' => Carbon::parse('2019-12-31'),
            'end' => Carbon::parse('2020-01-07'),
        ], $periods[52]);
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
            ->end(now()->subSecond())
            ->groupByWeek()
            ->get();

        $expected = [
            [
                'value' => 5,
                'increments' => +3,
                'decrements' => -1,
                'difference' => 2,
                'start' => now()->subWeeks(2),
                'end' => now()->subWeeks(1),
            ],
            [
                'value' => 5,
                'increments' => +3,
                'decrements' => -1,
                'difference' => 2,
                'start' => now()->subWeeks(1),
                'end' => now(),
            ],
        ];

        $this->assertEquals($expected, $stats->toArray());
    }

    /** @test */
    public function it_can_get_stats_2()
    {

        (new OrderStats())->increase(100, now()->subMonth());
        (new OrderStats())->decrease(1, now()->subDays(13));
        (new OrderStats())->increase(3, now()->subDays(12));
        (new OrderStats())->decrease(1, now()->subDays(5));
        (new OrderStats())->increase(3, now()->subDays(4));

        $stats = Stats::for(OrderStats::class)
            ->start(now()->subWeeks(2))
            ->end(now()->subSecond())
            ->groupByWeek()
            ->get();

        $expected = [
            [
                'value' => 102,
                'increments' => +3,
                'decrements' => -1,
                'difference' => 2,
                'start' => now()->subWeeks(2),
                'end' => now()->subWeeks(1),
            ],
            [
                'value' => 104,
                'increments' => +3,
                'decrements' => -1,
                'difference' => 2,
                'start' => now()->subWeeks(1),
                'end' => now(),
            ],
        ];

        $this->assertEquals($expected, $stats->toArray());
    }
    /** @test */
    public function it_can_get_stats_3()
    {

        (new OrderStats())->increase(3, now()->subDays(12));

        $stats = Stats::for(OrderStats::class)
            ->start(now()->subWeeks(2))
            ->end(now()->subSecond())
            ->groupByWeek()
            ->get();

        $expected = [
            [
                'value' => 3,
                'increments' => +3,
                'decrements' => 0,
                'difference' => 3,
                'start' => now()->subWeeks(2),
                'end' => now()->subWeeks(1),
            ],
            [
                'value' => 3,
                'increments' => 0,
                'decrements' => 0,
                'difference' => 0,
                'start' => now()->subWeeks(1),
                'end' => now(),
            ],
        ];

        $this->assertEquals($expected, $stats->toArray());
    }

    /** @test */
    public function it_can_get_stats_4()
    {
        $stats = Stats::for(OrderStats::class)
            ->start(now()->subWeeks(2))
            ->end(now()->subSecond())
            ->groupByWeek()
            ->get();

        $expected = [
            [
                'value' => 0,
                'increments' => 0,
                'decrements' => 0,
                'difference' => 0,
                'start' => now()->subWeeks(2),
                'end' => now()->subWeeks(1),
            ],
            [
                'value' => 0,
                'increments' => 0,
                'decrements' => 0,
                'difference' => 0,
                'start' => now()->subWeeks(1),
                'end' => now(),
            ],
        ];

        $this->assertEquals($expected, $stats->toArray());
    }
}
