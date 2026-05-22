<?php

namespace VWoody\DataMasking\Contracts;

interface MasksFields
{
    /** @return array<int, string> */
    public function maskedFields(): array;
}
