<?php

namespace VWoody\DataMasking\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use VWoody\DataMasking\DataMaskingService;

/**
 * @method static mixed unmasked(Closure $callback)
 * @method static bool shouldMask()
 * @method static array<string, mixed> maskArray(array $data, object|string $target)
 * @method static string maskValue(string $value, string $maskerClass)
 *
 * @see DataMaskingService
 */
class DataMasking extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DataMaskingService::class;
    }
}
