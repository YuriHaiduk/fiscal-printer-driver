<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter\Protocol;

enum PaymentType: string
{
    case Cash = '0';
    case Card = '1';
    case Credit = '2';
    case Voucher = '3';
}
