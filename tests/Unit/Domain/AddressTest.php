<?php

declare(strict_types=1);

use App\Domain\Shipping\Exceptions\InvalidZipcodeException;
use App\Domain\Shipping\ValueObjects\Address;

describe('Address', function (): void {

    it('accepts 8-digit zipcode', function (): void {
        $address = new Address('01310100');

        expect($address->zipcode)->toBe('01310100');
    });

    it('accepts formatted zipcode and strips dash', function (): void {
        $address = new Address('01310-100');

        expect($address->zipcode)->toBe('01310100');
    });

    it('formats zipcode with dash', function (): void {
        $address = new Address('01310100');

        expect($address->formatted())->toBe('01310-100');
    });

    it('throws on zipcode with fewer than 8 digits', function (): void {
        new Address('1234567');
    })->throws(InvalidZipcodeException::class);

    it('throws on zipcode with letters', function (): void {
        new Address('ABCDE-FGH');
    })->throws(InvalidZipcodeException::class);

    it('considers equal zipcodes as equal', function (): void {
        $a = new Address('01310100');
        $b = new Address('01310-100');

        expect($a->equals($b))->toBeTrue();
    });

    it('considers different zipcodes as not equal', function (): void {
        $a = new Address('01310100');
        $b = new Address('20040020');

        expect($a->equals($b))->toBeFalse();
    });
});
