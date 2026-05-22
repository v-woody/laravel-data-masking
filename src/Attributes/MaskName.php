<?php

namespace JamieWood\DataMasking\Attributes;

use Attribute;
use JamieWood\DataMasking\Maskers\NameMasker;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MaskName extends Mask
{
    public function __construct()
    {
        parent::__construct(masker: NameMasker::class);
    }
}
