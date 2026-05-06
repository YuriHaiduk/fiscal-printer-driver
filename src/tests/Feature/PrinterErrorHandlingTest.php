<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\FiscalPrinter\Exceptions\PrinterErrorException;
use App\Services\FiscalPrinter\FpMoldovaM1Driver;
use App\Services\FiscalPrinter\Frame\BccCalculator;
use App\Services\FiscalPrinter\Frame\Bytes;
use App\Services\FiscalPrinter\Frame\FrameBuilder;
use App\Services\FiscalPrinter\Frame\FrameParser;
use App\Services\FiscalPrinter\Frame\SequenceGenerator;
use App\Services\FiscalPrinter\Protocol\PrinterErrorCode;
use App\Services\FiscalPrinter\Protocol\VatGroup;
use App\Services\FiscalPrinter\Transport\MockSerialConnection;
use Tests\TestCase;

final class PrinterErrorHandlingTest extends TestCase
{
    public function test_it_throws_exception_when_printer_returns_error_frame(): void
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

        // Response DATA = C0 45 32
        // C0 = status byte, 45 32 = ASCII "E2"
        // Full frame: STX SEQ LEN CMD DATA ETX BCC
        $connection->pushResponse(Bytes::fromHex('02 20 04 00 31 C0 45 32 03 A1'));

        try {
            $driver->registerSale(
                name: 'Bread',
                vatGroup: VatGroup::A,
                priceCents: 500,
                quantityMillis: 1000,
            );

            $this->fail('Expected PrinterErrorException was not thrown.');
        } catch (PrinterErrorException $exception) {
            $this->assertSame(
                PrinterErrorCode::CommandSequenceViolation,
                $exception->printerErrorCode()
            );

            $this->assertSame(0xC0, $exception->status()->raw());
        }
    }
}
