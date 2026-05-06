<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\FiscalPrinter\FpMoldovaM1Driver;
use App\Services\FiscalPrinter\Frame\BccCalculator;
use App\Services\FiscalPrinter\Frame\FrameBuilder;
use App\Services\FiscalPrinter\Frame\FrameParser;
use App\Services\FiscalPrinter\Frame\SequenceGenerator;
use App\Services\FiscalPrinter\Protocol\PaymentType;
use App\Services\FiscalPrinter\Protocol\VatGroup;
use App\Services\FiscalPrinter\Transport\MockSerialConnection;
use InvalidArgumentException;
use Tests\TestCase;

final class PayloadValidationTest extends TestCase
{
    private function makeDriver(): FpMoldovaM1Driver
    {
        return new FpMoldovaM1Driver(
            connection: new MockSerialConnection(),
            frameBuilder: new FrameBuilder(
                bccCalculator: new BccCalculator(),
                sequenceGenerator: new SequenceGenerator(start: 0x20),
            ),
            frameParser: new FrameParser(new BccCalculator()),
        );
    }

    public function test_it_rejects_sale_item_name_longer_than_36_ascii_characters(): void
    {
        $driver = $this->makeDriver();

        $this->expectException(InvalidArgumentException::class);

        $driver->registerSale(
            name: str_repeat('A', 37),
            vatGroup: VatGroup::A,
            priceCents: 500,
            quantityMillis: 1000,
        );
    }

    public function test_it_rejects_sale_item_name_with_tab(): void
    {
        $driver = $this->makeDriver();

        $this->expectException(InvalidArgumentException::class);

        $driver->registerSale(
            name: "Bread\tFresh",
            vatGroup: VatGroup::A,
            priceCents: 500,
            quantityMillis: 1000,
        );
    }

    public function test_it_rejects_sale_item_name_with_nul_byte(): void
    {
        $driver = $this->makeDriver();

        $this->expectException(InvalidArgumentException::class);

        $driver->registerSale(
            name: "Bread\0Fresh",
            vatGroup: VatGroup::A,
            priceCents: 500,
            quantityMillis: 1000,
        );
    }

    public function test_it_rejects_non_ascii_sale_item_name(): void
    {
        $driver = $this->makeDriver();

        $this->expectException(InvalidArgumentException::class);

        $driver->registerSale(
            name: 'Хліб',
            vatGroup: VatGroup::A,
            priceCents: 500,
            quantityMillis: 1000,
        );
    }

    public function test_it_rejects_price_out_of_range(): void
    {
        $driver = $this->makeDriver();

        $this->expectException(InvalidArgumentException::class);

        $driver->registerSale(
            name: 'Bread',
            vatGroup: VatGroup::A,
            priceCents: 0,
            quantityMillis: 1000,
        );
    }

    public function test_it_rejects_quantity_out_of_range(): void
    {
        $driver = $this->makeDriver();

        $this->expectException(InvalidArgumentException::class);

        $driver->registerSale(
            name: 'Bread',
            vatGroup: VatGroup::A,
            priceCents: 500,
            quantityMillis: 0,
        );
    }

    public function test_it_rejects_payment_amount_out_of_range(): void
    {
        $driver = $this->makeDriver();

        $this->expectException(InvalidArgumentException::class);

        $driver->payment(
            paymentType: PaymentType::Cash,
            amountCents: 0,
        );
    }
}
