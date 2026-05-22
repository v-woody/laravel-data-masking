<?php

namespace JamieWood\DataMasking\Maskers;

use JamieWood\DataMasking\Contracts\Masker;

class StringMasker implements Masker
{
    public function mask(string $value): string
    {
        $length = strlen($value);

        if ($length <= 1) {
            return '*';
        }

        return $value[0] . str_repeat('*', $length - 1);
    }
}
