<?php

namespace VWoody\DataMasking\Maskers;

use VWoody\DataMasking\Contracts\Masker;

class NameMasker implements Masker
{
    public function mask(string $value): string
    {
        $parts = explode(' ', trim($value));

        $maskedParts = array_map(function (string $part): string {
            return $this->maskNamePart($part);
        }, $parts);

        return implode(' ', $maskedParts);
    }

    private function maskNamePart(string $part): string
    {
        $length = strlen($part);

        if ($length <= 1) {
            return $part;
        }

        return $part[0] . str_repeat('*', $length - 1);
    }
}
