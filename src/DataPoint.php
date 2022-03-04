<?php

namespace Spatie\Stats;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

class DataPoint implements Arrayable
{
    const TYPE_SET = 'set';
    const TYPE_CHANGE = 'change';

    public function __construct(
        public Carbon $start,
        public Carbon $end,
        public int $value,
        public int $increments,
        public int $decrements,
        public int $difference,
    ) {
    }

    public function toArray(): array
    {
        return (array) $this;
    }
}
