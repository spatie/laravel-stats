<?php

namespace Spatie\Stats\Tests;

use Carbon\Carbon;
use Spatie\Stats\Tests\Stats\OrderStats;

class BaseStatsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2020-01-01');
    }

    /** @test */
    public function it_can_create_events_for_increments()
    {
        $stats = new OrderStats();

        $stats->increase();

        $this->assertDatabaseHas('stats_events', [
            'name' => 'OrderStats',
            'value' => 1,
            'type' => 'change',
        ]);
    }

    /** @test */
    public function it_can_create_events_for_increments_at_a_given_timestamp()
    {
        $stats = new OrderStats();

        $stats->increase(1, now()->subWeek());

        $this->assertDatabaseHas('stats_events', [
            'name' => 'OrderStats',
            'value' => 1,
            'type' => 'change',
            'created_at' => now()->subWeek(),
        ]);
    }

    /** @test */
    public function it_can_create_events_for_decrements()
    {
        $stats = new OrderStats();

        $stats->decrease(1);

        $this->assertDatabaseHas('stats_events', [
            'name' => 'OrderStats',
            'value' => -1,
            'type' => 'change',
        ]);
    }

    /** @test */
    public function it_can_create_events_for_decrements_at_a_given_timestamp()
    {
        $stats = new OrderStats();

        $stats->decrease(1, now()->subWeek());

        $this->assertDatabaseHas('stats_events', [
            'name' => 'OrderStats',
            'value' => -1,
            'type' => 'change',
            'created_at' => now()->subWeek(),
        ]);
    }

    /** @test */
    public function it_can_create_events_for_setting_fixed_values()
    {
        $stats = new OrderStats();

        $stats->set(1337);

        $this->assertDatabaseHas('stats_events', [
            'name' => 'OrderStats',
            'value' => 1337,
            'type' => 'set',
        ]);
    }

    /** @test */
    public function it_can_create_events_for_setting_fixed_values_at_a_given_timestamp()
    {
        $stats = new OrderStats();

        $stats->set(1337, now()->subWeek());

        $this->assertDatabaseHas('stats_events', [
            'name' => 'OrderStats',
            'value' => 1337,
            'type' => 'set',
            'created_at' => now()->subWeek(),
        ]);
    }

    /** @test */
    public function it_can_get_the_value_at_a_given_time()
    {
        OrderStats::set(3, now()->subDays(19));
        OrderStats::decrease(1, now()->subDays(4));
        OrderStats::increase(3, now()->subDays(2));

        $this->assertEquals(0, OrderStats::query()->getValue(now()->subDays(30)));
        $this->assertEquals(3, OrderStats::query()->getValue(now()->subDays(18)));
        $this->assertEquals(5, OrderStats::query()->getValue(now()));
    }
}
