<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter\Protocol;

enum Command: int
{
    case GetStatus = 0x4A;
    case GetSerialNumber = 0x4B;
    case GetFiscalCounter = 0x4C;

    case OpenFiscalReceipt = 0x30;
    case RegisterSale = 0x31;
    case Subtotal = 0x33;
    case Payment = 0x35;
    case CloseFiscalReceipt = 0x38;
    case CancelReceipt = 0x39;
}
