<?php

namespace JamieWood\DataMasking\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Mask
{
    public function __construct(
        public readonly string $masker,
    ) {}
}
