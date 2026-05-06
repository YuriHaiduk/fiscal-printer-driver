<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\FiscalPrinter\Contracts\FiscalPrinterDriver;
use App\Services\FiscalPrinter\FpMoldovaM1Driver;
use Tests\TestCase;

final class FiscalPrinterContainerTest extends TestCase
{
    public function test_it_resolves_fiscal_printer_driver_from_container(): void
    {
        $driver = app(FiscalPrinterDriver::class);

        $this->assertInstanceOf(FpMoldovaM1Driver::class, $driver);
    }
}
