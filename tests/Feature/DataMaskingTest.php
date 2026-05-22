<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Gate;
use VWoody\DataMasking\Attributes\MaskEmail;
use VWoody\DataMasking\Attributes\MaskPhone;
use VWoody\DataMasking\Concerns\HasMaskedAttributes;
use VWoody\DataMasking\Contracts\MasksFields;
use VWoody\DataMasking\DataMaskingService;
use VWoody\DataMasking\Maskers\EmailMasker;
use VWoody\DataMasking\Maskers\PhoneMasker;
use VWoody\DataMasking\MaskingRegistry;

// Helper models for tests
class UserWithAttributes extends Model
{
    use HasMaskedAttributes;

    protected $fillable = ['email', 'phone', 'name'];

    #[MaskEmail]
    public string $email = '';

    #[MaskPhone]
    public string $phone = '';
}

class UserWithInterface extends Model implements MasksFields
{
    use HasMaskedAttributes;

    protected $fillable = ['email', 'phone'];

    public function maskedFields(): array
    {
        return [
            'email:'.EmailMasker::class,
            'phone:'.PhoneMasker::class,
        ];
    }
}

class AuthUser extends Authenticatable
{
    protected $guarded = [];
}

// MaskingRegistry tests
test('registry resolves maskers from php attributes', function () {
    $registry = new MaskingRegistry;

    $maskers = $registry->resolveFor(UserWithAttributes::class);

    expect($maskers)->toHaveKeys(['email', 'phone']);
    expect($maskers['email'])->toBeInstanceOf(EmailMasker::class);
    expect($maskers['phone'])->toBeInstanceOf(PhoneMasker::class);
});

test('registry resolves maskers from interface', function () {
    $registry = new MaskingRegistry;

    $user = new UserWithInterface;
    $maskers = $registry->resolveFor($user);

    expect($maskers)->toHaveKeys(['email', 'phone']);
});

test('registry resolves maskers from config', function () {
    $registry = new MaskingRegistry;

    $registry->setConfigRules([
        UserWithAttributes::class => [
            'email' => EmailMasker::class,
        ],
    ]);

    $maskers = $registry->resolveFor(UserWithAttributes::class);

    expect($maskers)->toHaveKey('email');
});

test('attributes take priority over config rules', function () {
    $registry = new MaskingRegistry;

    $registry->setConfigRules([
        UserWithAttributes::class => [
            'email' => PhoneMasker::class,
        ],
    ]);

    $maskers = $registry->resolveFor(UserWithAttributes::class);

    expect($maskers['email'])->toBeInstanceOf(EmailMasker::class);
});

// DataMaskingService tests
test('service masks array using resolved maskers', function () {
    $service = app(DataMaskingService::class);

    $data = ['email' => 'jamie@example.com', 'name' => 'Jamie'];
    $masked = $service->maskArray($data, UserWithAttributes::class);

    expect($masked['email'])->toBe('j****@*******.com');
    expect($masked['name'])->toBe('Jamie');
});

test('service skips masking when bypass gate allows it', function () {
    $authUser = new AuthUser(['id' => 1]);

    Gate::define('bypass-masking', fn (AuthUser $user) => true);

    config(['data-masking.bypass_gate' => 'bypass-masking']);

    $service = app(DataMaskingService::class);

    $this->actingAs($authUser);

    $data = ['email' => 'jamie@example.com'];
    $masked = $service->maskArray($data, UserWithAttributes::class);

    expect($masked['email'])->toBe('jamie@example.com');
});

test('service applies masking when bypass gate denies', function () {
    config(['data-masking.bypass_gate' => 'bypass-masking']);

    Gate::define('bypass-masking', fn () => false);

    $service = app(DataMaskingService::class);

    $data = ['email' => 'jamie@example.com'];
    $masked = $service->maskArray($data, UserWithAttributes::class);

    expect($masked['email'])->toBe('j****@*******.com');
});

test('unmasked callback bypasses masking', function () {
    $service = app(DataMaskingService::class);

    $result = $service->unmasked(function () use ($service) {
        $data = ['email' => 'jamie@example.com'];

        return $service->maskArray($data, UserWithAttributes::class);
    });

    expect($result['email'])->toBe('jamie@example.com');
});

test('masking resumes after unmasked callback', function () {
    $service = app(DataMaskingService::class);

    $service->unmasked(fn () => null);

    expect($service->shouldMask())->toBeTrue();
});

test('service skips null values', function () {
    $service = app(DataMaskingService::class);

    $data = ['email' => null];
    $masked = $service->maskArray($data, UserWithAttributes::class);

    expect($masked['email'])->toBeNull();
});
