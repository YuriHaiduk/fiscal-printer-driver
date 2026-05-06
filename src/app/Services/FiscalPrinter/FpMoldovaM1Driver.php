<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter;

use App\Services\FiscalPrinter\Contracts\FiscalPrinterDriver;
use App\Services\FiscalPrinter\Contracts\SerialConnection;
use App\Services\FiscalPrinter\Frame\Frame;
use App\Services\FiscalPrinter\Frame\FrameBuilder;
use App\Services\FiscalPrinter\Frame\FrameParser;
use App\Services\FiscalPrinter\Protocol\Command;
use App\Services\FiscalPrinter\Protocol\PaymentType;
use App\Services\FiscalPrinter\Protocol\StatusByte;
use App\Services\FiscalPrinter\Protocol\VatGroup;
use InvalidArgumentException;
use App\Services\FiscalPrinter\Exceptions\PrinterErrorException;
use App\Services\FiscalPrinter\Protocol\PrinterErrorCode;

final readonly class FpMoldovaM1Driver implements FiscalPrinterDriver
{
    public function __construct(
        private SerialConnection $connection,
        private FrameBuilder $frameBuilder,
        private FrameParser $frameParser,
    ) {
    }

    public function getStatus(): StatusByte
    {
        $response = $this->sendCommand(Command::GetStatus);

        return StatusByte::fromByte($response->statusByte());
    }

    public function openFiscalReceipt(
        string $operatorId,
        string $password,
        string $terminalId,
    ): void {
        $payload = sprintf('%s;%s;%s', $operatorId, $password, $terminalId);

        $this->sendCommand(Command::OpenFiscalReceipt, $payload);
    }

    public function registerSale(
        string $name,
        VatGroup $vatGroup,
        int $priceCents,
        int $quantityMillis,
    ): void {
        $this->assertAscii($name, 'Sale item name');

        if (strlen($name) > 36) {
            throw new InvalidArgumentException('Sale item name must not exceed 36 ASCII characters.');
        }

        if (str_contains($name, "\t") || str_contains($name, "\0")) {
            throw new InvalidArgumentException('Sale item name must not contain TAB or NUL characters.');
        }

        if ($priceCents < 1 || $priceCents > 999999) {
            throw new InvalidArgumentException('Price must be between 0.01 and 9999.99.');
        }

        if ($quantityMillis < 1 || $quantityMillis > 999999) {
            throw new InvalidArgumentException('Quantity must be between 0.001 and 999.999.');
        }

        $payload = implode("\t", [
            $name,
            $vatGroup->value,
            $this->formatMoney($priceCents),
            $this->formatQuantity($quantityMillis),
        ]);

        $this->sendCommand(Command::RegisterSale, $payload);
    }

    public function payment(
        PaymentType $paymentType,
        int $amountCents,
    ): void {
        if ($amountCents < 1 || $amountCents > 999999) {
            throw new InvalidArgumentException('Amount must be between 0.01 and 9999.99.');
        }

        $payload = sprintf(
            '%s;%s',
            $paymentType->value,
            $this->formatMoney($amountCents)
        );

        $this->sendCommand(Command::Payment, $payload);
    }

    public function closeFiscalReceipt(): void
    {
        $this->sendCommand(Command::CloseFiscalReceipt);
    }

    private function sendCommand(Command $command, string $payload = ''): Frame
    {
        $frame = $this->frameBuilder->build(
            command: $command->value,
            payload: $payload,
        );

        $this->connection->write($frame);

        $response = $this->frameParser->parse(
            $this->connection->read()
        );

        $this->throwIfPrinterReturnedError($response);

        return $response;
    }

    private function throwIfPrinterReturnedError(Frame $response): void
    {
        $data = $response->data;

        if (strlen($data) < 3) {
            return;
        }

        $errorPayload = substr($data, 1, 2);

        if (! str_starts_with($errorPayload, 'E')) {
            return;
        }

        throw new PrinterErrorException(
            printerErrorCode: PrinterErrorCode::fromPayload($errorPayload),
            status: StatusByte::fromByte($response->statusByte()),
        );
    }

    private function formatMoney(int $cents): string
    {
        return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }

    private function formatQuantity(int $millis): string
    {
        return sprintf('%d.%03d', intdiv($millis, 1000), $millis % 1000);
    }

    private function assertAscii(string $value, string $field): void
    {
        if (! mb_check_encoding($value, 'ASCII')) {
            throw new InvalidArgumentException($field . ' must contain ASCII characters only.');
        }
    }
}
