<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter\Contracts;

use App\Services\FiscalPrinter\Protocol\PaymentType;
use App\Services\FiscalPrinter\Protocol\StatusByte;
use App\Services\FiscalPrinter\Protocol\VatGroup;

interface FiscalPrinterDriver
{
    public function getStatus(): StatusByte;

    public function openFiscalReceipt(
        string $operatorId,
        string $password,
        string $terminalId,
    ): void;

    public function registerSale(
        string $name,
        VatGroup $vatGroup,
        int $priceCents,
        int $quantityMillis,
    ): void;

    public function payment(
        PaymentType $paymentType,
        int $amountCents,
    ): void;

    public function closeFiscalReceipt(): void;
}
