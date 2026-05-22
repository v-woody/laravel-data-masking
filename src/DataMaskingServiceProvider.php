<?php

namespace VWoody\DataMasking;

use Illuminate\Support\ServiceProvider;
use VWoody\DataMasking\Commands\VerifyMaskingCommand;

class DataMaskingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/data-masking.php',
            'data-masking'
        );

        $this->app->singleton(MaskingRegistry::class, function () {
            $registry = new MaskingRegistry;

            $registry->setConfigRules(config('data-masking.models', []));

            return $registry;
        });

        $this->app->scoped(DataMaskingService::class, function ($app) {
            return new DataMaskingService(
                registry: $app->make(MaskingRegistry::class),
            );
        });
    }

    public function boot(): void
    {
        $this->commands([
            VerifyMaskingCommand::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/data-masking.php' => config_path('data-masking.php'),
            ], 'data-masking-config');
        }
    }
}
