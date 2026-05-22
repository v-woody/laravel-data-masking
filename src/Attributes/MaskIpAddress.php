<?php

namespace VWoody\DataMasking\Attributes;

use Attribute;
use VWoody\DataMasking\Maskers\IpAddressMasker;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MaskIpAddress extends Mask
{
    public function __construct()
    {
        parent::__construct(masker: IpAddressMasker::class);
    }
}
