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
        return StatsQuery::for(StatsEvent::class, [
            'name' => (new static)->getName(),
        ]);
    }

    public static function writer(): StatsWriter
    {
        return StatsWriter::for(StatsEvent::class, [
            'name' => (new static)->getName(),
        ]);
    }

    public static function increase(mixed $number = 1, ?DateTimeInterface $timestamp = null)
    {
        static::writer()->increase($number, $timestamp);
    }

    public static function decrease(mixed $number = 1, ?DateTimeInterface $timestamp = null)
    {
        static::writer()->decrease($number, $timestamp);
    }

    public static function set(int $value, ?DateTimeInterface $timestamp = null)
    {
        static::writer()->set($value, $timestamp);
    }
}
