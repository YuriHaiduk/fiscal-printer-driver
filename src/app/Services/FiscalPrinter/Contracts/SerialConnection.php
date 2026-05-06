<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter\Contracts;

interface SerialConnection
{
    public function write(string $bytes): void;

    public function read(): string;
}
