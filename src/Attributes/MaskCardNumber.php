<?php

namespace VWoody\DataMasking\Attributes;

use Attribute;
use VWoody\DataMasking\Maskers\CardNumberMasker;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MaskCardNumber extends Mask
{
    public function __construct()
    {
        parent::__construct(masker: CardNumberMasker::class);
    }
}
