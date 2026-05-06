<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\FiscalPrinter\FpMoldovaM1Driver;
use App\Services\FiscalPrinter\Frame\BccCalculator;
use App\Services\FiscalPrinter\Frame\Bytes;
use App\Services\FiscalPrinter\Frame\FrameBuilder;
use App\Services\FiscalPrinter\Frame\FrameParser;
use App\Services\FiscalPrinter\Frame\SequenceGenerator;
use App\Services\FiscalPrinter\Protocol\PaymentType;
use App\Services\FiscalPrinter\Protocol\VatGroup;
use App\Services\FiscalPrinter\Transport\MockSerialConnection;
use Tests\TestCase;

final class SaleFlowTest extends TestCase
{
    public function test_it_generates_expected_sale_flow_frames(): void
    {
        $connection = new MockSerialConnection();

        $driver = new FpMoldovaM1Driver(
            connection: $connection,
            frameBuilder: new FrameBuilder(
                bccCalculator: new BccCalculator(),
                sequenceGenerator: new SequenceGenerator(start: 0x20),
            ),
            frameParser: new FrameParser(new BccCalculator()),
        );

        $connection->pushResponse(Bytes::fromHex('02 20 02 00 30 C0 03 D1'));
        $connection->pushResponse(Bytes::fromHex('02 21 02 00 31 C0 03 D1'));
        $connection->pushResponse(Bytes::fromHex('02 22 02 00 35 C0 03 D6'));
        $connection->pushResponse(Bytes::fromHex('02 23 02 00 38 C0 03 DA'));

        $driver->openFiscalReceipt(
            operatorId: '1',
            password: '0000',
            terminalId: '01',
        );

        $driver->registerSale(
            name: 'Bread',
            vatGroup: VatGroup::A,
            priceCents: 500,
            quantityMillis: 2000,
        );

        $driver->payment(
            paymentType: PaymentType::Cash,
            amountCents: 1000,
        );

        $driver->closeFiscalReceipt();

        $frames = array_map(
            static fn (string $frame): string => Bytes::toHex($frame),
            $connection->writtenFrames(),
        );

        $this->assertSame([
            '02 20 0A 00 30 31 3B 30 30 30 30 3B 30 31 03 29',
            '02 21 13 00 31 42 72 65 61 64 09 41 09 35 2E 30 30 09 32 2E 30 30 30 03 2F',
            '02 22 08 00 35 30 3B 31 30 2E 30 30 03 38',
            '02 23 01 00 38 03 19',
        ], $frames);
    }
}
