<?php

namespace VWoody\DataMasking\Contracts;

interface Masker
{
    public function mask(string $value): string;
}
