<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use VWoody\DataMasking\DataMaskingServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [DataMaskingServiceProvider::class];
    }
}
