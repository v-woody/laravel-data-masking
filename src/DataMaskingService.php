<?php

namespace JamieWood\DataMasking;

use Closure;
use Illuminate\Support\Facades\Gate;
use JamieWood\DataMasking\Contracts\Masker;

class DataMaskingService
{
    private bool $forceBypassed = false;

    public function __construct(
        private readonly MaskingRegistry $registry,
    ) {}

    /**
     * Execute a callback with masking disabled, regardless of gate checks.
     */
    public function unmasked(Closure $callback): mixed
    {
        $this->forceBypassed = true;

        try {
            return $callback();
        } finally {
            $this->forceBypassed = false;
        }
    }

    /**
     * Determine whether masking should be applied for the current request context.
     */
    public function shouldMask(): bool
    {
        if ($this->forceBypassed) {
            return false;
        }

        $bypassGate = config('data-masking.bypass_gate');

        if ($bypassGate === null) {
            return true;
        }

        if (! Gate::has($bypassGate)) {
            return true;
        }

        return ! Gate::allows($bypassGate);
    }

    /**
     * Apply masking to all relevant fields on the given array using the target class for resolution.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function maskArray(array $data, object|string $target): array
    {
        if (! $this->shouldMask()) {
            return $data;
        }

        $maskers = $this->registry->resolveFor($target);

        foreach ($maskers as $field => $masker) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            if ($data[$field] === null) {
                continue;
            }

            $data[$field] = $masker->mask((string) $data[$field]);
        }

        return $data;
    }

    /**
     * Mask a single value using a specific masker class.
     */
    public function maskValue(string $value, string $maskerClass): string
    {
        if (! $this->shouldMask()) {
            return $value;
        }

        /** @var Masker $masker */
        $masker = app($maskerClass);

        return $masker->mask($value);
    }

    public function getRegistry(): MaskingRegistry
    {
        return $this->registry;
    }
}
