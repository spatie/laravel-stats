<?php

namespace Spatie\Stats\Tests;

use Carbon\Carbon;
use Spatie\Stats\Stats;
use Spatie\Stats\Tests\Stats\OrderStats;

class BaseStatsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2020-01-01');
    }

    /** @test */
    public function it_can_get_the_value_at_a_given_time()
    {
        OrderStats::set(3, now()->subDays(19));
        OrderStats::decrease(1, now()->subDays(4));
        OrderStats::increase(3, now()->subDays(2));

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
            Carbon::parse('2019-01-01'),
            Carbon::parse('2019-01-08'),
        ], $periods[0]);

        $this->assertEquals([
            Carbon::parse('2019-12-31'),
            Carbon::parse('2020-01-07'),
        ], $periods[52]);
    }

    /** @test */
    public function it_can_get_stats()
    {
        OrderStats::set(3, now()->subMonth());
        OrderStats::decrease(1, now()->subDays(13));
        OrderStats::increase(3, now()->subDays(12));
        OrderStats::set(3, now()->subDays(6));
        OrderStats::decrease(1, now()->subDays(5));
        OrderStats::increase(3, now()->subDays(4));

        $stats = Stats::for(OrderStats::class)
            ->start(now()->subWeeks(2))
            ->end(now()->subSecond())
            ->groupByWeek()
            ->get();

        $expected = [
            [
                'value' => 5,
                'increments' => +3,
                'decrements' => 1,
                'difference' => 2,
                'start' => now()->subWeeks(2),
                'end' => now()->subWeeks(1),
            ],
            [
                'value' => 5,
                'increments' => +3,
                'decrements' => 1,
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
        OrderStats::increase(100, now()->subMonth());
        OrderStats::decrease(1, now()->subDays(13));
        OrderStats::increase(3, now()->subDays(12));
        OrderStats::decrease(1, now()->subDays(5));
        OrderStats::increase(3, now()->subDays(4));

        $stats = Stats::for(OrderStats::class)
            ->start(now()->subWeeks(2))
            ->end(now()->subSecond())
            ->groupByWeek()
            ->get();

        $expected = [
            [
                'value' => 102,
                'increments' => +3,
                'decrements' => 1,
                'difference' => 2,
                'start' => now()->subWeeks(2),
                'end' => now()->subWeeks(1),
            ],
            [
                'value' => 104,
                'increments' => +3,
                'decrements' => 1,
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
        OrderStats::increase(3, now()->subDays(12));

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

    /** @test */
    public function it_can_get_stats_grouped_by_day()
    {
        OrderStats::set(3, now()->subDays(6));
        OrderStats::decrease(1, now()->subDays(2));
        OrderStats::increase(3, now()->subDays(1));

        $stats = Stats::for(OrderStats::class)
            ->start(now()->subDays(3))
            ->end(now())
            ->groupByDay()
            ->get();

        $expected = [
            [
                'value' => 3,
                'increments' => 0,
                'decrements' => 0,
                'difference' => 0,
                'start' => now()->subDays(3),
                'end' => now()->subDays(2),
            ],
            [
                'value' => 2,
                'increments' => 0,
                'decrements' => 1,
                'difference' => -1,
                'start' => now()->subDays(2),
                'end' => now()->subDays(1),
            ],
            [
                'value' => 5,
                'increments' => 3,
                'decrements' => 0,
                'difference' => 3,
                'start' => now()->subDays(1),
                'end' => now(),
            ],
        ];

        $this->assertEquals($expected, $stats->toArray());
    }

    /** @test */
    public function it_can_get_stats_grouped_by_hour()
    {
        OrderStats::set(3, now()->subHours(6));
        OrderStats::decrease(1, now()->subHours(2));
        OrderStats::increase(3, now()->subHours(1));

        $stats = Stats::for(OrderStats::class)
            ->start(now()->subHours(3))
            ->end(now())
            ->groupByHour()
            ->get();

        $expected = [
            [
                'value' => 3,
                'increments' => 0,
                'decrements' => 0,
                'difference' => 0,
                'start' => now()->subHours(3),
                'end' => now()->subHours(2),
            ],
            [
                'value' => 2,
                'increments' => 0,
                'decrements' => 1,
                'difference' => -1,
                'start' => now()->subHours(2),
                'end' => now()->subHours(1),
            ],
            [
                'value' => 5,
                'increments' => 3,
                'decrements' => 0,
                'difference' => 3,
                'start' => now()->subHours(1),
                'end' => now(),
            ],
        ];

        $this->assertEquals($expected, $stats->toArray());
    }
}
