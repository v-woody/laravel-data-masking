<?php

namespace JamieWood\DataMasking\Attributes;

use Attribute;
use JamieWood\DataMasking\Maskers\IpAddressMasker;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MaskIpAddress extends Mask
{
    public function __construct()
    {
        parent::__construct(masker: IpAddressMasker::class);
    }
}
