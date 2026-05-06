<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\FiscalPrinter\FpMoldovaM1Driver;
use App\Services\FiscalPrinter\Frame\BccCalculator;
use App\Services\FiscalPrinter\Frame\Bytes;
use App\Services\FiscalPrinter\Frame\FrameBuilder;
use App\Services\FiscalPrinter\Frame\FrameParser;
use App\Services\FiscalPrinter\Frame\SequenceGenerator;
use App\Services\FiscalPrinter\Protocol\PaymentType;
use App\Services\FiscalPrinter\Protocol\VatGroup;
use App\Services\FiscalPrinter\Transport\MockSerialConnection;
use Illuminate\Console\Command;

final class DemoFiscalPrinterSaleCommand extends Command
{
    protected $signature = 'fp-moldova:demo-sale';

    protected $description = 'Run a demo fiscal sale flow using mock serial connection.';

    public function handle(): int
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

        /*
         * Mock successful printer responses.
         *
         * Response frame format:
         * STX SEQ LEN CMD DATA ETX BCC
         *
         * DATA contains Status Byte as the first byte.
         * 0xC0 = RESERVED + LAST_CMD_OK.
         */
        $connection->pushResponse(Bytes::fromHex('02 20 02 00 30 C0 03 D1')); // OpenFiscalReceipt
        $connection->pushResponse(Bytes::fromHex('02 21 02 00 31 C0 03 D1')); // RegisterSale
        $connection->pushResponse(Bytes::fromHex('02 22 02 00 35 C0 03 D6')); // Payment
        $connection->pushResponse(Bytes::fromHex('02 23 02 00 38 C0 03 DA')); // CloseFiscalReceipt

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

        $this->info('Demo sale flow completed.');
        $this->newLine();

        $this->line('Frames written to mock serial connection:');
        $this->newLine();

        foreach ($connection->writtenFrames() as $index => $frame) {
            $this->line(sprintf(
                '%d. %s',
                $index + 1,
                Bytes::toHex($frame)
            ));
        }

        return self::SUCCESS;
    }
}
