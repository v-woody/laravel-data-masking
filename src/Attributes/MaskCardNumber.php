<?php

namespace JamieWood\DataMasking\Attributes;

use Attribute;
use JamieWood\DataMasking\Maskers\CardNumberMasker;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MaskCardNumber extends Mask
{
    public function __construct()
    {
        parent::__construct(masker: CardNumberMasker::class);
    }
}
