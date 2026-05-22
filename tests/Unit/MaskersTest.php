<?php

use VWoody\DataMasking\Maskers\CardNumberMasker;
use VWoody\DataMasking\Maskers\CustomMasker;
use VWoody\DataMasking\Maskers\EmailMasker;
use VWoody\DataMasking\Maskers\IpAddressMasker;
use VWoody\DataMasking\Maskers\NameMasker;
use VWoody\DataMasking\Maskers\PhoneMasker;

// EmailMasker
test('email masker masks local part and domain', function () {
    $masker = new EmailMasker;

    expect($masker->mask('jamie@example.com'))->toBe('j****@*******.com');
});

test('email masker handles single character local part', function () {
    $masker = new EmailMasker;

    expect($masker->mask('j@example.com'))->toBe('*@*******.com');
});

test('email masker handles missing at sign', function () {
    $masker = new EmailMasker;

    $masked = $masker->mask('notanemail');

    expect($masked)->toBe('**********');
});

// PhoneMasker
test('phone masker keeps last four digits', function () {
    $masker = new PhoneMasker;

    expect($masker->mask('07911123456'))->toBe('*******3456');
});

test('phone masker handles short numbers', function () {
    $masker = new PhoneMasker;

    expect($masker->mask('123'))->toBe('***');
});

test('phone masker preserves formatting', function () {
    $masker = new PhoneMasker;

    expect($masker->mask('+44 7911 123456'))->toBe('+** **** **3456');
});

// NameMasker
test('name masker masks each part after first character', function () {
    $masker = new NameMasker;

    expect($masker->mask('Jamie Woodruff'))->toBe('J**** W*******');
});

test('name masker handles single name', function () {
    $masker = new NameMasker;

    expect($masker->mask('Jamie'))->toBe('J****');
});

// CardNumberMasker
test('card number masker keeps last four digits and preserves formatting', function () {
    $masker = new CardNumberMasker;

    expect($masker->mask('4111 1111 1111 1234'))->toBe('**** **** **** 1234');
});

test('card number masker handles unformatted number', function () {
    $masker = new CardNumberMasker;

    expect($masker->mask('4111111111111234'))->toBe('************1234');
});

// IpAddressMasker
test('ipv4 masker keeps first two octets', function () {
    $masker = new IpAddressMasker;

    expect($masker->mask('192.168.1.100'))->toBe('192.168.*.*');
});

test('ipv6 masker keeps first two groups', function () {
    $masker = new IpAddressMasker;

    expect($masker->mask('2001:db8:85a3:0:0:8a2e:370:7334'))->toBe('2001:db8:****:****:****:****:****:****');
});

// CustomMasker
test('custom masker applies callback', function () {
    $masker = new CustomMasker(fn (string $value) => str_repeat('X', strlen($value)));

    expect($masker->mask('secret'))->toBe('XXXXXX');
});

test('custom masker throws when callback does not return string', function () {
    $masker = new CustomMasker(fn () => 123);

    expect(fn () => $masker->mask('value'))->toThrow(InvalidArgumentException::class);
});
