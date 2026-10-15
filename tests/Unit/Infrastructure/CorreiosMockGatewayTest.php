<?php

declare(strict_types=1);

use App\Domain\Shipping\ValueObjects\Address;
use App\Domain\Shipping\ValueObjects\Dimensions;
use App\Domain\Shipping\ValueObjects\Weight;
use App\Infrastructure\Carriers\CorreiosPacGateway;
use App\Infrastructure\Carriers\CorreiosSedexGateway;
use App\Infrastructure\Carriers\JadLogGateway;
use App\Infrastructure\Carriers\TotalExpressGateway;

describe('Carrier Gateways', function (): void {

    beforeEach(function (): void {
        $this->origin      = new Address('01310100');
        $this->destination = new Address('20040020');
        $this->weight      = new Weight(2.0);
        $this->dimensions  = new Dimensions(20, 15, 10);
    });

    it('CorreiosPac returns correct carrier and service names', function (): void {
        $gateway = new CorreiosPacGateway();

        expect($gateway->getCarrierName())->toBe('Correios')
            ->and($gateway->getServiceName())->toBe('PAC');
    });

    it('CorreiosPac calculates price within expected range for 2kg', function (): void {
        // base = 2*3.50 + 8.00 = 15.00 — variation ±10%
        $gateway = new CorreiosPacGateway();
        $result  = $gateway->calculateRate($this->origin, $this->destination, $this->weight, $this->dimensions);

        expect($result->price->toFloat())->toBeGreaterThanOrEqual(13.50)
            ->and($result->price->toFloat())->toBeLessThanOrEqual(16.50)
            ->and($result->deliveryDays)->toBeGreaterThanOrEqual(6)
            ->and($result->deliveryDays)->toBeLessThanOrEqual(8);
    });

    it('CorreiosSedex returns correct carrier and service names', function (): void {
        $gateway = new CorreiosSedexGateway();

        expect($gateway->getCarrierName())->toBe('Correios')
            ->and($gateway->getServiceName())->toBe('SEDEX');
    });

    it('CorreiosSedex calculates price within expected range for 2kg', function (): void {
        // base = 2*7.00 + 15.00 = 29.00 — variation ±10%
        $gateway = new CorreiosSedexGateway();
        $result  = $gateway->calculateRate($this->origin, $this->destination, $this->weight, $this->dimensions);

        expect($result->price->toFloat())->toBeGreaterThanOrEqual(26.10)
            ->and($result->price->toFloat())->toBeLessThanOrEqual(31.90)
            ->and($result->deliveryDays)->toBeGreaterThanOrEqual(1)
            ->and($result->deliveryDays)->toBeLessThanOrEqual(3);
    });

    it('JadLog returns correct carrier and service names', function (): void {
        $gateway = new JadLogGateway();

        expect($gateway->getCarrierName())->toBe('JadLog')
            ->and($gateway->getServiceName())->toBe('Expresso');
    });

    it('TotalExpress returns correct carrier and service names', function (): void {
        $gateway = new TotalExpressGateway();

        expect($gateway->getCarrierName())->toBe('TotalExpress')
            ->and($gateway->getServiceName())->toBe('Standard');
    });

    it('result always has positive delivery days', function (): void {
        $gateways = [
            new CorreiosPacGateway(),
            new CorreiosSedexGateway(),
            new JadLogGateway(),
            new TotalExpressGateway(),
        ];

        foreach ($gateways as $gateway) {
            $result = $gateway->calculateRate($this->origin, $this->destination, $this->weight, $this->dimensions);

            expect($result->deliveryDays)->toBeGreaterThanOrEqual(1);
        }
    });
});
