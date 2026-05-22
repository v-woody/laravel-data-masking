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
        //     'email'       => \JamieWood\DataMasking\Maskers\EmailMasker::class,
        //     'phone'       => \JamieWood\DataMasking\Maskers\PhoneMasker::class,
        //     'card_number' => \JamieWood\DataMasking\Maskers\CardNumberMasker::class,
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
    | In config/logging.php add: 'tap' => [\JamieWood\DataMasking\Log\MaskingTap::class]
    |
    */
    'log_fields' => [
        // 'email'       => \JamieWood\DataMasking\Maskers\EmailMasker::class,
        // 'password'    => \JamieWood\DataMasking\Maskers\StringMasker::class,
        // 'ip_address'  => \JamieWood\DataMasking\Maskers\IpAddressMasker::class,
    ],

];
