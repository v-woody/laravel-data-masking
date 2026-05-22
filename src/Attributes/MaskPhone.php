<?php

namespace VWoody\DataMasking\Attributes;

use Attribute;
use VWoody\DataMasking\Maskers\PhoneMasker;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MaskPhone extends Mask
{
    public function __construct()
    {
        parent::__construct(masker: PhoneMasker::class);
    }
}
