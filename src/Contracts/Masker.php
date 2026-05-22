<?php

namespace JamieWood\DataMasking\Contracts;

interface Masker
{
    public function mask(string $value): string;
}
