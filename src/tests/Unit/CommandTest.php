<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\FiscalPrinter\Protocol\Command;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    public function test_it_defines_query_command_codes(): void
    {
        $this->assertSame(0x4A, Command::GetStatus->value);
        $this->assertSame(0x4B, Command::GetSerialNumber->value);
        $this->assertSame(0x4C, Command::GetFiscalCounter->value);
    }

    public function test_it_defines_fiscal_receipt_command_codes(): void
    {
        $this->assertSame(0x30, Command::OpenFiscalReceipt->value);
        $this->assertSame(0x31, Command::RegisterSale->value);
        $this->assertSame(0x33, Command::Subtotal->value);
        $this->assertSame(0x35, Command::Payment->value);
        $this->assertSame(0x38, Command::CloseFiscalReceipt->value);
        $this->assertSame(0x39, Command::CancelReceipt->value);
    }
}
