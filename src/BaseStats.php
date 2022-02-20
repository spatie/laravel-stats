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

        StatsWriter::for(StatsEvent::class)->increase($number, ['name' => $model->getName()], $timestamp);
    }

    public static function decrease(mixed $number = 1, ?DateTimeInterface $timestamp = null)
    {
        $model = new static();

        StatsWriter::for(StatsEvent::class)->decrease($number, ['name' => $model->getName()], $timestamp);
    }

    public static function set(int $value, ?DateTimeInterface $timestamp = null)
    {
        $model = new static();

        StatsWriter::for(StatsEvent::class)->set($value, ['name' => $model->getName()], $timestamp);
    }
}
