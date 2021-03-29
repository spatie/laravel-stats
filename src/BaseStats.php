<?php

namespace Spatie\Stats;

use DateTimeInterface;
use Spatie\Stats\Models\StatsEvent;

abstract class BaseStats
{
    public function getKey(): string
    {
        return class_basename($this);
    }

    public static function increase(int $number = 1, ?DateTimeInterface $timestamp = null)
    {
        $stats = new static;

        $stats->createEvent(StatsEvent::TYPE_CHANGE, $number, $timestamp);
    }

    public static function decrease(int $number = 1, ?DateTimeInterface $timestamp = null)
    {
        $stats = new static;

        $stats->createEvent(StatsEvent::TYPE_CHANGE, -$number, $timestamp);
    }

    public static function set(int $value, ?DateTimeInterface $timestamp = null)
    {
        $stats = new static;

        $stats->createEvent(StatsEvent::TYPE_SET, $value, $timestamp);
    }

    protected function createEvent($type, $value, ?DateTimeInterface $timestamp = null): StatsEvent
    {
        return StatsEvent::create([
            'name' => $this->getKey(),
            'type' => $type,
            'value' => $value,
            'created_at' => $timestamp ?? now(),
        ]);
    }
}
