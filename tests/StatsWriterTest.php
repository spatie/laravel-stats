<?php

namespace Spatie\Stats\Tests;

use Carbon\Carbon;
use Spatie\Stats\Models\StatsEvent;
use Spatie\Stats\StatsWriter;
use Spatie\Stats\Tests\Stats\CustomerStats;
use Spatie\Stats\Tests\Stats\OrderStats;
use Spatie\Stats\Tests\TestClasses\Models\Stat;

class StatsWriterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2020-01-01');
    }

    /** @test */
    public function it_can_write_with_base_stats_extensions()
    {
        CustomerStats::increase();
        OrderStats::increase();

        $this->assertDatabaseHas('stats_events', [
            'name' => 'CustomerStats',
            'value' => 1,
            'type' => 'change',
        ]);

        $this->assertDatabaseHas('stats_events', [
            'name' => 'OrderStats',
            'value' => 1,
            'type' => 'change',
        ]);
    }

    /** @test */
    public function it_can_create_events_for_class_names()
    {
        StatsWriter::for(StatsEvent::class)->increase();

        $this->assertDatabaseHas('stats_events', [
            'value' => 1,
            'type' => 'change',
        ]);
    }

    /** @test */
    public function it_can_create_events_for_model_instances()
    {
        StatsWriter::for(new StatsEvent())->increase(1);

        $this->assertDatabaseHas('stats_events', [
            'value' => 1,
            'type' => 'change',
        ]);
    }

    /** @test */
    public function it_can_create_events_for_has_many_relationships()
    {
        /** @var Stat $stats */
        $stats = Stat::create();

        StatsWriter::for($stats->events())->increase();

        $this->assertDatabaseHas('stats_events', [
            'stat_id' => $stats->getKey(),
            'value' => 1,
            'type' => 'change',
        ]);
    }

    /** @test */
    public function it_can_create_events_for_has_many_relationships_with_custom_attributes()
    {
        /** @var Stat $stats */
        $stats = Stat::create();

        StatsWriter::for($stats->events(), ['name' => 'recurring'])->increase(1);

        $this->assertDatabaseHas('stats_events', [
            'stat_id' => $stats->getKey(),
            'name' => 'recurring',
            'value' => 1,
            'type' => 'change',
        ]);
    }

    /** @test */
    public function it_can_create_events_with_custom_attributes()
    {
        StatsWriter::for(new StatsEvent(), ['name' => 'OrderStats'])->increase(1);

        $this->assertDatabaseHas('stats_events', [
            'name' => 'OrderStats',
            'value' => 1,
            'type' => 'change',
        ]);
    }

    /** @test */
    public function it_can_create_events_for_increments_at_a_given_timestamp()
    {
        StatsWriter::for(StatsEvent::class)->increase(1, now()->subWeek());

        $this->assertDatabaseHas('stats_events', [
            'value' => 1,
            'type' => 'change',
            'created_at' => now()->subWeek(),
        ]);
    }

    /** @test */
    public function it_can_create_events_for_increments()
    {
        StatsWriter::for(StatsEvent::class)->increase();

        $this->assertDatabaseHas('stats_events', [
            'value' => 1,
            'type' => 'change',
        ]);
    }

    /** @test */
    public function it_can_create_events_for_decrements()
    {
        StatsWriter::for(StatsEvent::class)->decrease();

        $this->assertDatabaseHas('stats_events', [
            'value' => -1,
            'type' => 'change',
        ]);
    }

    /** @test */
    public function it_can_create_events_for_decrements_at_a_given_timestamp()
    {
        StatsWriter::for(StatsEvent::class)->decrease(1, now()->subWeek());

        $this->assertDatabaseHas('stats_events', [
            'value' => -1,
            'type' => 'change',
            'created_at' => now()->subWeek(),
        ]);
    }

    /** @test */
    public function it_can_create_events_for_setting_fixed_values()
    {
        StatsWriter::for(StatsEvent::class)->set(1337);

        $this->assertDatabaseHas('stats_events', [
            'value' => 1337,
            'type' => 'set',
        ]);
    }

    /** @test */
    public function it_can_create_events_for_setting_fixed_values_at_a_given_timestamp()
    {
        StatsWriter::for(StatsEvent::class)->set(1337, now()->subWeek());

        $this->assertDatabaseHas('stats_events', [
            'value' => 1337,
            'type' => 'set',
            'created_at' => now()->subWeek(),
        ]);
    }

    /** @test */
    public function it_can_pass_and_receive_attributes()
    {
        $writer = StatsWriter::for(StatsEvent::class, ['customer_attrib' => 'custom_val']);

        $this->assertInstanceOf(StatsWriter::class, $writer);
        $this->assertSame(['customer_attrib' => 'custom_val'], $writer->getAttributes());
    }
}
