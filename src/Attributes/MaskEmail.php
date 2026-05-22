<?php

namespace JamieWood\DataMasking\Attributes;

use Attribute;
use JamieWood\DataMasking\Maskers\EmailMasker;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MaskEmail extends Mask
{
    public function __construct()
    {
        parent::__construct(masker: EmailMasker::class);
    }
}
