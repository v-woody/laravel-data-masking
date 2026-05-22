<?php

namespace VWoody\DataMasking\Maskers;

use Closure;
use InvalidArgumentException;
use VWoody\DataMasking\Contracts\Masker;

class CustomMasker implements Masker
{
    private Closure $callback;

    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    public function mask(string $value): string
    {
        $result = ($this->callback)($value);

        if (! is_string($result)) {
            throw new InvalidArgumentException('Custom masker callback must return a string.');
        }

        return $result;
    }
}
