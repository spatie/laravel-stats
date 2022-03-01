<?php

namespace Spatie\Stats\Tests;

use Carbon\Carbon;
use Spatie\Stats\Models\Stat;
use Spatie\Stats\Models\StatsEvent;
use Spatie\Stats\StatsWriter;
use Spatie\Stats\Tests\Stats\CustomerStats;
use Spatie\Stats\Tests\Stats\OrderStats;

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

//    /** @test */
//    public function it_can_get_the_value_at_a_given_time()
//    {
//        OrderStats::set(3, now()->subDays(19));
//        OrderStats::decrease(1, now()->subDays(4));
//        OrderStats::increase(3, now()->subDays(2));
//
//        $this->assertEquals(0, StatsQuery::for(OrderStats::class)->getValue(now()->subDays(30)));
//        $this->assertEquals(3, StatsQuery::for(OrderStats::class)->getValue(now()->subDays(18)));
//        $this->assertEquals(5, StatsQuery::for(OrderStats::class)->getValue(now()));
//    }
//
//    /** @test */
//    public function it_can_generate_and_array_of_periods()
//    {
//        $periods = StatsQuery::for(OrderStats::class)->start(now()->subYear())->end(now())->generatePeriods();
//
//        $this->assertCount(53, $periods);
//
//        $this->assertEquals([
//            Carbon::parse('2018-12-31'),
//            Carbon::parse('2019-01-07'),
//            '201901',
//        ], $periods[0]);
//
//        $this->assertEquals([
//            Carbon::parse('2019-12-30'),
//            Carbon::parse('2020-01-06'),
//            '202001',
//        ], $periods[52]);
//    }
}
