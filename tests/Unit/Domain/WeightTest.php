<?php

declare(strict_types=1);

use App\Domain\Shipping\Exceptions\InvalidWeightException;
use App\Domain\Shipping\ValueObjects\Weight;

describe('Weight', function (): void {

    it('accepts valid weight', function (): void {
        $weight = new Weight(2.5);

        expect($weight->kg)->toBe(2.5);
    });

    it('throws on zero weight', function (): void {
        new Weight(0.0);
    })->throws(InvalidWeightException::class);

    it('throws on negative weight', function (): void {
        new Weight(-1.0);
    })->throws(InvalidWeightException::class);

    it('throws when weight exceeds 30kg', function (): void {
        new Weight(30.001);
    })->throws(InvalidWeightException::class);

    it('accepts exactly 30kg', function (): void {
        $weight = new Weight(30.0);

        expect($weight->kg)->toBe(30.0);
    });

    it('converts to grams', function (): void {
        $weight = new Weight(1.5);

        expect($weight->inGrams())->toBe(1500);
    });

    it('rounds to 3 decimal places', function (): void {
        $weight = new Weight(1.23456);

        expect($weight->kg)->toBe(1.235);
    });
});
