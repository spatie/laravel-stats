<?php

namespace Spatie\Stats;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class StatsWriter
{
    private Model|Relation|string $subject;
    private array $attributes;

    public function __construct(Model|Relation|string $subject, array $attributes = [])
    {
        $this->subject = $subject;
        $this->attributes = $attributes;
    }

    public static function for(Model|Relation|string $subject, array $attributes = [])
    {
        return new static($subject, $attributes);
    }

    public function increase(mixed $number = 1, ?DateTimeInterface $timestamp = null)
    {
        $number = is_int($number) ? $number : 1;

        $this->createEvent(DataPoint::TYPE_CHANGE, $number, $timestamp);
    }

    public function decrease(mixed $number = 1, ?DateTimeInterface $timestamp = null)
    {
        $number = is_int($number) ? $number : 1;

        $this->createEvent(DataPoint::TYPE_CHANGE, -$number, $timestamp);
    }

    public function set(int $value, ?DateTimeInterface $timestamp = null)
    {
        $this->createEvent(DataPoint::TYPE_SET, $value, $timestamp);
    }

    protected function createEvent($type, $value, ?DateTimeInterface $timestamp = null): Model
    {
        if ($this->subject instanceof Relation) {
            return $this->subject->create(array_merge($this->attributes, [
                'type' => $type,
                'value' => $value,
                'created_at' => $timestamp ?? now(),
            ]));
        }

        $subject = $this->subject;
        if ($subject instanceof Model) {
            $subject = get_class($subject);
        }

        return $subject::create(array_merge($this->attributes, [
            'type' => $type,
            'value' => $value,
            'created_at' => $timestamp ?? now(),
        ]));
    }
}
