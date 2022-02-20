<?php

namespace Spatie\Stats;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\Stats\Models\StatsEvent;

class StatsWriter
{
    private Model|Relation|string $subject;

    public function __construct(Model|Relation|string $subject)
    {
        $this->subject = $subject;
    }

    public static function for($subject)
    {
        return new static($subject);
    }

    public function increase(mixed $number = 1, array $attributes = [], ?DateTimeInterface $timestamp = null)
    {
        $number = is_int($number) ? $number : 1;

        $this->createEvent(DataPoint::TYPE_CHANGE, $number, $attributes, $timestamp);
    }

    public function decrease(mixed $number = 1, array $attributes = [], ?DateTimeInterface $timestamp = null)
    {
        $number = is_int($number) ? $number : 1;

        $this->createEvent(DataPoint::TYPE_CHANGE, -$number, $attributes, $timestamp);
    }

    public function set(int $value, array $attributes = [], ?DateTimeInterface $timestamp = null)
    {
        $this->createEvent(DataPoint::TYPE_SET, $value, $attributes, $timestamp);
    }

    protected function createEvent($type, $value, array $attributes = [], ?DateTimeInterface $timestamp = null): StatsEvent
    {
        if ($this->subject instanceof Relation) {
            return $this->subject->create(array_merge($attributes, [
                'type' => $type,
                'value' => $value,
                'created_at' => $timestamp ?? now(),
            ]));
        }

        $subject = $this->subject;
        if ($subject instanceof Model) {
            $subject = get_class($subject);
        }

        return $subject::create(array_merge($attributes, [
            'type' => $type,
            'value' => $value,
            'created_at' => $timestamp ?? now(),
        ]));
    }
}
