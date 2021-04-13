<?php

namespace Spatie\Stats;

use DateTimeInterface;
use Spatie\Stats\Models\StatsEvent;

abstract class BaseStats
{
    public function getName(): string
    {
        return class_basename($this);
    }

    public static function query(): StatsQuery
    {
        return new StatsQuery(static::class);
    }

    public static function increase(mixed $number = 1, ?DateTimeInterface $timestamp = null)
    {
        $number = is_int($number) ? $number : 1;

        $stats = new static;

        $stats->createEvent(StatsEvent::TYPE_CHANGE, $number, $timestamp);
    }

    public static function decrease(mixed $number = 1, ?DateTimeInterface $timestamp = null)
    {
        $number = is_int($number) ? $number : 1;

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
            'name' => $this->getName(),
            'type' => $type,
            'value' => $value,
            'created_at' => $timestamp ?? now(),
        ]);
    }
}
