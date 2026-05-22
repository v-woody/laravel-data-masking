<?php

namespace JamieWood\DataMasking\Maskers;

use JamieWood\DataMasking\Contracts\Masker;

class IpAddressMasker implements Masker
{
    public function mask(string $value): string
    {
        if ($this->isIpv6($value)) {
            return $this->maskIpv6($value);
        }

        return $this->maskIpv4($value);
    }

    private function isIpv6(string $value): bool
    {
        return str_contains($value, ':');
    }

    private function maskIpv4(string $value): string
    {
        $parts = explode('.', $value);

        if (count($parts) !== 4) {
            return str_repeat('*', strlen($value));
        }

        return "{$parts[0]}.{$parts[1]}.*.*";
    }

    private function maskIpv6(string $value): string
    {
        $parts = explode(':', $value);
        $visibleParts = array_slice($parts, 0, 2);

        $maskedParts = array_map(fn () => '****', array_slice($parts, 2));

        return implode(':', array_merge($visibleParts, $maskedParts));
    }
}
