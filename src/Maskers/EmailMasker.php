<?php

namespace JamieWood\DataMasking\Maskers;

use JamieWood\DataMasking\Contracts\Masker;

class EmailMasker implements Masker
{
    public function mask(string $value): string
    {
        if (! str_contains($value, '@')) {
            return str_repeat('*', strlen($value));
        }

        [$localPart, $domain] = explode('@', $value, 2);

        $maskedLocal = $this->maskLocalPart($localPart);
        $maskedDomain = $this->maskDomain($domain);

        return "{$maskedLocal}@{$maskedDomain}";
    }

    private function maskLocalPart(string $localPart): string
    {
        $length = strlen($localPart);

        if ($length <= 1) {
            return '*';
        }

        return $localPart[0] . str_repeat('*', $length - 1);
    }

    private function maskDomain(string $domain): string
    {
        if (! str_contains($domain, '.')) {
            return str_repeat('*', strlen($domain));
        }

        $dotPosition = strrpos($domain, '.');
        $tld = substr($domain, $dotPosition);
        $domainName = substr($domain, 0, $dotPosition);

        return str_repeat('*', strlen($domainName)) . $tld;
    }
}
