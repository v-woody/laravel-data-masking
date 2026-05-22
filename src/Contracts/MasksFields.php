<?php

namespace JamieWood\DataMasking\Contracts;

interface MasksFields
{
    /** @return array<int, string> */
    public function maskedFields(): array;
}
