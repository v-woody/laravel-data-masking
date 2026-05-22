# Laravel Data Masking

Automatically mask sensitive fields in Eloquent model serialisation, API resources, and log context. Built for PII compliance (GDPR, CCPA, HIPAA) without requiring you to change how you use your models.

## Requirements

- PHP 8.2 or higher
- Laravel 10, 11, 12, or 13

## Installation

```bash
composer require jamiewood/laravel-data-masking
```

The service provider and facade are registered automatically via Laravel's package discovery.

Optionally publish the config file:

```bash
php artisan vendor:publish --tag=data-masking-config
```

## How It Works

When a model serialises to an array (via `toArray()`, `toJson()`, or a JSON API response), the package intercepts the output and replaces sensitive field values with masked equivalents. The same masking can be applied to Laravel log context entries via a Monolog processor.

Masking rules can be defined in three ways, applied in priority order:

1. **PHP 8 attributes** on model properties (highest priority)
2. **`MasksFields` interface** returning an array of field definitions
3. **Config file rules** per model class (lowest priority)

## Usage

### Option 1: PHP 8 Attributes

Add an attribute directly to a model property. No other configuration is needed.

```php
use VWoody\DataMasking\Concerns\HasMaskedAttributes;
use VWoody\DataMasking\Attributes\MaskEmail;
use VWoody\DataMasking\Attributes\MaskPhone;
use VWoody\DataMasking\Attributes\MaskName;

class User extends Model
{
    use HasMaskedAttributes;

    #[MaskEmail]
    public string $email;

    #[MaskPhone]
    public string $phone;

    #[MaskName]
    public string $full_name;
}
```

```php
$user->email;        // jamie@example.com  (original, direct access)
$user->toArray();    // ['email' => 'j****@*******.com', ...]
```

### Option 2: MasksFields Interface

Implement `MasksFields` and return a list of field definitions. Each entry is either `'field:MaskerClass'` or just `'field'` (which uses `StringMasker` as the default).

```php
use VWoody\DataMasking\Concerns\HasMaskedAttributes;
use VWoody\DataMasking\Contracts\MasksFields;
use VWoody\DataMasking\Maskers\EmailMasker;
use VWoody\DataMasking\Maskers\PhoneMasker;

class User extends Model implements MasksFields
{
    use HasMaskedAttributes;

    public function maskedFields(): array
    {
        return [
            'email:' . EmailMasker::class,
            'phone:' . PhoneMasker::class,
        ];
    }
}
```

### Option 3: Config File

Define masking rules per model in `config/data-masking.php`. Useful when you cannot or do not want to modify the model class directly.

```php
'models' => [
    App\Models\User::class => [
        'email'       => \VWoody\DataMasking\Maskers\EmailMasker::class,
        'phone'       => \VWoody\DataMasking\Maskers\PhoneMasker::class,
        'card_number' => \VWoody\DataMasking\Maskers\CardNumberMasker::class,
    ],
],
```

### Custom Masker

Create a class implementing the `Masker` contract to define your own masking logic.

```php
use VWoody\DataMasking\Contracts\Masker;

class NationalInsuranceMasker implements Masker
{
    public function mask(string $value): string
    {
        return substr($value, 0, 2) . '****' . substr($value, -1);
    }
}
```

You can then reference it anywhere a masker class is expected:

```php
#[Mask(NationalInsuranceMasker::class)]
public string $ni_number;
```

Or use `CustomMasker` inline for one-off programmatic use:

```php
use VWoody\DataMasking\Maskers\CustomMasker;

$masker = new CustomMasker(fn (string $value) => str_repeat('*', strlen($value)));
$masker->mask('secret'); // '******'
```

Note: `CustomMasker` requires a `Closure` and cannot be resolved from the container, so it is not suitable for use in config or attributes. Create a dedicated class for those cases.

## Built-in Maskers

| Class | Example input | Example output |
|---|---|---|
| `EmailMasker` | `jamie@example.com` | `j****@*******.com` |
| `PhoneMasker` | `+44 7911 123456` | `+** **** **3456` |
| `NameMasker` | `Jamie Woodruff` | `J**** W*******` |
| `CardNumberMasker` | `4111 1111 1111 1234` | `**** **** **** 1234` |
| `IpAddressMasker` | `192.168.1.100` | `192.168.*.*` |
| `StringMasker` | `mysecret` | `m*******` |
| `CustomMasker` | any string | closure-defined output |

## API Resource Masking

Extend `MaskedJsonResource` instead of `JsonResource` to apply masking automatically when your model is serialised through an API resource.

```php
use VWoody\DataMasking\Resources\MaskedJsonResource;

class UserResource extends MaskedJsonResource
{
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
```

Masking rules are resolved from the underlying `$this->resource` model using the same attribute, interface, and config priority as everywhere else.

## Log Masking

Add the `MaskingTap` to any logging channel in `config/logging.php`:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single'],
        'tap' => [\VWoody\DataMasking\Log\MaskingTap::class],
    ],
],
```

Then define which context fields to mask in `config/data-masking.php`:

```php
'log_fields' => [
    'email'      => \VWoody\DataMasking\Maskers\EmailMasker::class,
    'ip_address' => \VWoody\DataMasking\Maskers\IpAddressMasker::class,
    'password'   => \VWoody\DataMasking\Maskers\StringMasker::class,
],
```

Any log call that includes these keys in its context will have them masked automatically, including nested arrays.

```php
Log::info('User logged in', ['email' => 'jamie@example.com']);
// Logs: User logged in {"email":"j****@*******.com"}
```

## Bypassing Masking

### Gate-based bypass (recommended)

Define a gate name in the config. When the gate passes for the current user, masking is skipped. This is the recommended approach for admin panels or internal tooling.

```php
// config/data-masking.php
'bypass_gate' => 'view-unmasked-data',
```

```php
// App\Providers\AuthServiceProvider
Gate::define('view-unmasked-data', fn (User $user) => $user->isAdmin());
```

### Callback bypass

Wrap any code in `DataMasking::unmasked()` to disable masking for the duration of that callback, regardless of gate state.

```php
use VWoody\DataMasking\Facades\DataMasking;

$rawData = DataMasking::unmasked(fn () => $user->toArray());
```

Note: when running under Laravel Octane or Swoole, `DataMasking::unmasked()` is safe to use because the service is scoped per request.

## Testing

```bash
composer test
```

## Linting

```bash
composer lint
```

## Contributing

Contributions are welcome. Please follow these steps:

1. Fork the repository on GitHub.
2. Create a branch for your change: `git checkout -b feature/your-feature-name`
3. Write tests for your change. All existing tests must continue to pass.
4. Run the test suite: `composer test`
5. Run the linter and fix any issues: `composer lint`
6. Open a pull request against the `main` branch with a clear description of what the change does and why.

### Guidelines

- Follow the coding standards used throughout the package (PSR-12, Spatie Laravel guidelines).
- Keep pull requests focused. One feature or fix per PR.
- Do not break backwards compatibility without discussion in an issue first.
- New maskers should implement the `Masker` contract and include unit tests covering normal input, edge cases, and empty or short values.

### Reporting Issues

Open an issue on GitHub. Include a description of the problem, the Laravel and PHP version you are using, and a minimal reproduction case where possible.

## Security

If you discover a security vulnerability, please report it privately by emailing jamie.woodruff@proton.me rather than opening a public issue. Do not disclose the details publicly until a fix has been released.

## Licence

The MIT Licence. See the [LICENCE](LICENCE) file for details.
