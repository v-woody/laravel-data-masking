<?php

namespace JamieWood\DataMasking\Attributes;

use Attribute;
use JamieWood\DataMasking\Maskers\PhoneMasker;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MaskPhone extends Mask
{
    public function __construct()
    {
        parent::__construct(masker: PhoneMasker::class);
    }
}
