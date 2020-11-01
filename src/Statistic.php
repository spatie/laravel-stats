<?php

namespace Spatie\Statistics;

use DateTimeInterface;
use Illuminate\Support\Str;
use Spatie\Statistics\Models\StatisticEvent;

abstract class Statistic
{
    public function getKey(): string
    {
        return class_basename($this);
    }

    public function getLabel(): string
    {
        return Str::title($this->getKey());
    }

    public function increase(int $number = 1, ?DateTimeInterface $timestamp = null)
    {
        $this->createEvent('change', $number, $timestamp);
    }

    public function decrease(int $number = 1, ?DateTimeInterface $timestamp = null)
    {
        $this->createEvent('change', -$number, $timestamp);
    }

    public function set(int $value, ?DateTimeInterface $timestamp = null)
    {
        $this->createEvent('snapshot', $value, $timestamp);
    }

    protected function createEvent($type, $value, ?DateTimeInterface $timestamp = null): StatisticEvent
    {
        return StatisticEvent::create([
            'statistic' => $this->getKey(),
            'type' => $type,
            'value' => $value,
            'timestamp' => $timestamp ?? now(),
        ]);
    }
}
