<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter\Exceptions;

use App\Services\FiscalPrinter\Protocol\PrinterErrorCode;
use App\Services\FiscalPrinter\Protocol\StatusByte;
use RuntimeException;

final class PrinterErrorException extends RuntimeException
{
    public function __construct(
        private readonly PrinterErrorCode $printerErrorCode,
        private readonly StatusByte $status,
    ) {
        parent::__construct(sprintf(
            'Printer returned error %s: %s',
            $printerErrorCode->value,
            $printerErrorCode->description()
        ));
    }

    public function printerErrorCode(): PrinterErrorCode
    {
        return $this->printerErrorCode;
    }

    public function status(): StatusByte
    {
        return $this->status;
    }
}
