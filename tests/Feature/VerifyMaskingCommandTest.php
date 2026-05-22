<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\Model;
use VWoody\DataMasking\Attributes\MaskEmail;
use VWoody\DataMasking\Attributes\MaskPhone;
use VWoody\DataMasking\Commands\VerifyMaskingCommand;
use VWoody\DataMasking\Concerns\HasMaskedAttributes;
use VWoody\DataMasking\Contracts\MasksFields;
use VWoody\DataMasking\Maskers\EmailMasker;
use VWoody\DataMasking\Maskers\StringMasker;
use VWoody\DataMasking\MaskingRegistry;

class VerifyModel extends Model
{
    use HasMaskedAttributes;

    #[MaskEmail]
    public string $email = '';

    #[MaskPhone]
    public string $phone = '';
}

class VerifyModelWithInterface extends Model implements MasksFields
{
    use HasMaskedAttributes;

    public function maskedFields(): array
    {
        return [
            'email:'.EmailMasker::class,
            'name',
        ];
    }
}

class UnmaskedModel extends Model {}

beforeEach(function () {
    $this->app->forgetInstance(MaskingRegistry::class);

    $this->app->make(Kernel::class)->registerCommand(
        $this->app->make(VerifyMaskingCommand::class)
    );
});

test('command exits successfully for a model with attribute-based rules', function () {
    $this->artisan('data-masking:verify', ['model' => VerifyModel::class])
        ->expectsOutputToContain('2 field(s) will be masked')
        ->assertExitCode(0);
});

test('command output contains the model class name', function () {
    $this->artisan('data-masking:verify', ['model' => VerifyModel::class])
        ->expectsOutputToContain(VerifyModel::class)
        ->assertExitCode(0);
});

test('command exits successfully for a model with interface-based rules', function () {
    $this->artisan('data-masking:verify', ['model' => VerifyModelWithInterface::class])
        ->expectsOutputToContain('2 field(s) will be masked')
        ->assertExitCode(0);
});

test('command shows config as source when rules are defined in config', function () {
    config(['data-masking.models' => [
        VerifyModel::class => [
            'national_insurance' => StringMasker::class,
        ],
    ]]);

    // Forget the instance so the factory re-runs with the updated config
    $this->app->forgetInstance(MaskingRegistry::class);

    $this->artisan('data-masking:verify', ['model' => VerifyModel::class])
        ->expectsOutputToContain('national_insurance')
        ->assertExitCode(0);
});

test('command fails with error when model class does not exist', function () {
    $this->artisan('data-masking:verify', ['model' => 'App\\Models\\NonExistentModel'])
        ->expectsOutputToContain('does not exist')
        ->assertExitCode(1);
});

test('command warns when no masking rules are found on a model', function () {
    $this->artisan('data-masking:verify', ['model' => UnmaskedModel::class])
        ->expectsOutputToContain('No masking rules found')
        ->assertExitCode(0);
});

test('command resolves correct number of fields via the registry directly', function () {
    $registry = $this->app->make(MaskingRegistry::class);

    $results = $registry->resolveWithSources(VerifyModel::class);

    expect($results)->toHaveCount(2)
        ->toHaveKeys(['email', 'phone']);

    expect($results['email']['source'])->toBe('attribute');
    expect($results['phone']['source'])->toBe('attribute');
});

test('command registry returns correct source for interface-based rules', function () {
    $registry = $this->app->make(MaskingRegistry::class);

    $model = new VerifyModelWithInterface;
    $results = $registry->resolveWithSources($model);

    expect($results)->toHaveCount(2)
        ->toHaveKeys(['email', 'name']);

    expect($results['email']['source'])->toBe('interface');
    expect($results['name']['source'])->toBe('interface');
});

test('command registry returns correct source for config-based rules', function () {
    $registry = $this->app->make(MaskingRegistry::class);

    $registry->setConfigRules([
        VerifyModel::class => [
            'national_insurance' => StringMasker::class,
        ],
    ]);

    $results = $registry->resolveWithSources(VerifyModel::class);

    expect($results)->toHaveKey('national_insurance');
    expect($results['national_insurance']['source'])->toBe('config');
});
