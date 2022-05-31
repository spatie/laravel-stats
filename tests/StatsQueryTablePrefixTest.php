<?php

namespace Spatie\Stats\Tests;

use Carbon\Carbon;
use Spatie\Stats\Tests\TestClasses\Models\Stat;

class StatsQueryTablePrefixTest extends TestCase
{
    const PREFIXED_CONNECTION = 'prefixed';
    const PREFIX = 'test_';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPrefixedConnection();
        Carbon::setTestNow('2020-01-01');
    }

    /** @test */
    public function it_can_retrieve_a_table_prefix_of_a_model()
    {
        $subject = (new Stat())->setConnection(self::PREFIXED_CONNECTION);
        $this->assertEquals($subject->getQuery()->getGrammar()->getTablePrefix(), self::PREFIX);
    }

    /** @test */
    public function it_can_retrieve_a_table_prefix_of_a_relation()
    {
        $subject = (new Stat())->setConnection(self::PREFIXED_CONNECTION);
        $this->assertEquals($subject->events()->getQuery()->getGrammar()->getTablePrefix(), self::PREFIX);
    }

    protected function setUpPrefixedConnection()
    {
        $this->app['config']->set('database.connections.'.self::PREFIXED_CONNECTION, $this->app['config']->get('database.connections.mysql'));
        $this->app['config']->set('database.connections.'.self::PREFIXED_CONNECTION.'.prefix', self::PREFIX);
    }
}
