<?php

namespace VWoody\DataMasking\Maskers;

use VWoody\DataMasking\Contracts\Masker;

class PhoneMasker implements Masker
{
    public function mask(string $value): string
    {
        $digitsOnly = preg_replace('/\D/', '', $value);
        $digitCount = strlen($digitsOnly);

        if ($digitCount < 4) {
            return str_repeat('*', strlen($value));
        }

        $lastFour = substr($digitsOnly, -4);
        $maskedDigits = str_repeat('*', $digitCount - 4).$lastFour;

        return $this->reformat($maskedDigits, $value);
    }

    private function reformat(string $maskedDigits, string $original): string
    {
        $digitIndex = 0;
        $result = '';

        for ($charIndex = 0; $charIndex < strlen($original); $charIndex++) {
            if (ctype_digit($original[$charIndex])) {
                $result .= $maskedDigits[$digitIndex];
                $digitIndex++;
            } else {
                $result .= $original[$charIndex];
            }
        }

        return $result;
    }
}
