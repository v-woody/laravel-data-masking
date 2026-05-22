<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bypass Gate
    |--------------------------------------------------------------------------
    |
    | When set, masking will be skipped if the current user is authorised by
    | this gate. Set to null to always mask regardless of the user.
    |
    | Example: 'bypass_gate' => 'view-unmasked-data'
    |
    */
    'bypass_gate' => null,

    /*
    |--------------------------------------------------------------------------
    | Model Rules
    |--------------------------------------------------------------------------
    |
    | Define masking rules for specific Eloquent model classes. These are the
     | lowest priority. PHP attributes and MasksFields interface override them.
    |
    | Format: ClassName::class => ['field' => MaskerClass::class]
    |
    */
    'models' => [
        // App\Models\User::class => [
        //     'email'       => \VWoody\DataMasking\Maskers\EmailMasker::class,
        //     'phone'       => \VWoody\DataMasking\Maskers\PhoneMasker::class,
        //     'card_number' => \VWoody\DataMasking\Maskers\CardNumberMasker::class,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Fields
    |--------------------------------------------------------------------------
    |
    | Fields listed here will be masked automatically in any log context or
    | extra data. Add the MaskingTap to your logging channel to enable this.
    |
    | In config/logging.php add: 'tap' => [\VWoody\DataMasking\Log\MaskingTap::class]
    |
    */
    'log_fields' => [
        // 'email'       => \VWoody\DataMasking\Maskers\EmailMasker::class,
        // 'password'    => \VWoody\DataMasking\Maskers\StringMasker::class,
        // 'ip_address'  => \VWoody\DataMasking\Maskers\IpAddressMasker::class,
    ],

];
