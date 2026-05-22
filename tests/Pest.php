<?php

use JamieWood\DataMasking\DataMaskingServiceProvider;

uses(
    Orchestra\Testbench\TestCase::class,
)->in('Feature', 'Unit');

uses()->beforeEach(function () {
    //
})->in('Feature', 'Unit');

/**
 * Override getPackageProviders on the TestCase so the service provider is loaded.
 */
function defineEnvironment($app): void
{
    //
}

function getPackageProviders($app): array
{
    return [DataMaskingServiceProvider::class];
}
