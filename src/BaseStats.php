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

    public static function increase(mixed $number = 1, ?DateTimeInterface $timestamp = null)
    {
        $model = new static();

        StatsWriter::for(StatsEvent::class, ['name' => $model->getName()])->increase($number, $timestamp);
    }

    public static function decrease(mixed $number = 1, ?DateTimeInterface $timestamp = null)
    {
        $model = new static();

        StatsWriter::for(StatsEvent::class, ['name' => $model->getName()])->decrease($number, $timestamp);
    }

    public static function set(int $value, ?DateTimeInterface $timestamp = null)
    {
        $model = new static();

        StatsWriter::for(StatsEvent::class, ['name' => $model->getName()])->set($value, $timestamp);
    }
}
