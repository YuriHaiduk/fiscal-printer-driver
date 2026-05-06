<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter\Protocol;

enum VatGroup: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
}
