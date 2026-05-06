<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\FiscalPrinter\Protocol\StatusByte;
use PHPUnit\Framework\TestCase;

final class StatusByteTest extends TestCase
{
    public function test_it_decodes_idle_after_reset_like_state(): void
    {
        $status = StatusByte::fromByte(0x80);

        $this->assertSame(0x80, $status->raw());

        $this->assertFalse($status->paperOut());
        $this->assertFalse($status->coverOpen());
        $this->assertFalse($status->fiscalMemoryLow());
        $this->assertFalse($status->fiscalMemoryFull());
        $this->assertFalse($status->receiptOpen());
        $this->assertFalse($status->nonFiscalOpen());
        $this->assertFalse($status->lastCommandOk());

        $this->assertTrue($status->reserved());
        $this->assertTrue($status->isIdleAfterResetLikeState());
    }

    public function test_it_decodes_success_status(): void
    {
        $status = StatusByte::fromByte(0xC0);

        $this->assertTrue($status->reserved());
        $this->assertTrue($status->lastCommandOk());

        $this->assertFalse($status->paperOut());
        $this->assertFalse($status->coverOpen());
        $this->assertFalse($status->fiscalMemoryLow());
        $this->assertFalse($status->fiscalMemoryFull());
        $this->assertFalse($status->receiptOpen());
        $this->assertFalse($status->nonFiscalOpen());
        $this->assertFalse($status->isIdleAfterResetLikeState());
    }

    public function test_it_decodes_multiple_flags(): void
    {
        $status = StatusByte::fromByte(
            StatusByte::RESERVED
            | StatusByte::LAST_CMD_OK
            | StatusByte::RECEIPT_OPEN
            | StatusByte::PAPER_OUT
        );

        $this->assertTrue($status->reserved());
        $this->assertTrue($status->lastCommandOk());
        $this->assertTrue($status->receiptOpen());
        $this->assertTrue($status->paperOut());

        $this->assertFalse($status->coverOpen());
        $this->assertFalse($status->fiscalMemoryLow());
        $this->assertFalse($status->fiscalMemoryFull());
        $this->assertFalse($status->nonFiscalOpen());
    }

    public function test_it_detects_blocking_fiscal_error(): void
    {
        $paperOutStatus = StatusByte::fromByte(StatusByte::RESERVED | StatusByte::PAPER_OUT);
        $coverOpenStatus = StatusByte::fromByte(StatusByte::RESERVED | StatusByte::COVER_OPEN);
        $fiscalMemoryFullStatus = StatusByte::fromByte(StatusByte::RESERVED | StatusByte::FISCAL_MEM_FULL);
        $okStatus = StatusByte::fromByte(StatusByte::RESERVED | StatusByte::LAST_CMD_OK);

        $this->assertTrue($paperOutStatus->hasBlockingFiscalError());
        $this->assertTrue($coverOpenStatus->hasBlockingFiscalError());
        $this->assertTrue($fiscalMemoryFullStatus->hasBlockingFiscalError());
        $this->assertFalse($okStatus->hasBlockingFiscalError());
    }

    public function test_it_masks_value_to_single_byte(): void
    {
        $status = StatusByte::fromByte(0x1C0);

        $this->assertSame(0xC0, $status->raw());
        $this->assertTrue($status->reserved());
        $this->assertTrue($status->lastCommandOk());
    }

    public function test_it_exports_to_array(): void
    {
        $status = StatusByte::fromByte(0xC0);

        $this->assertSame([
            'raw' => 0xC0,
            'paper_out' => false,
            'cover_open' => false,
            'fiscal_memory_low' => false,
            'fiscal_memory_full' => false,
            'receipt_open' => false,
            'non_fiscal_open' => false,
            'last_command_ok' => true,
            'reserved' => true,
            'idle_after_reset_like_state' => false,
            'blocking_fiscal_error' => false,
        ], $status->toArray());
    }
}
