<?php

namespace JamieWood\DataMasking\Maskers;

use JamieWood\DataMasking\Contracts\Masker;

class CardNumberMasker implements Masker
{
    public function mask(string $value): string
    {
        $digitsOnly = preg_replace('/\D/', '', $value);
        $length = strlen($digitsOnly);

        if ($length < 4) {
            return str_repeat('*', $length);
        }

        $lastFour = substr($digitsOnly, -4);
        $maskedDigits = str_repeat('*', $length - 4) . $lastFour;

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
