<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter\Protocol;

final readonly class StatusByte
{
    public const PAPER_OUT = 0x01;
    public const COVER_OPEN = 0x02;
    public const FISCAL_MEM_LOW = 0x04;
    public const FISCAL_MEM_FULL = 0x08;
    public const RECEIPT_OPEN = 0x10;
    public const NON_FISCAL_OPEN = 0x20;
    public const LAST_CMD_OK = 0x40;
    public const RESERVED = 0x80;

    private function __construct(
        private int $value,
    ) {
    }

    public static function fromByte(int $value): self
    {
        return new self($value & 0xFF);
    }

    public function raw(): int
    {
        return $this->value;
    }

    public function paperOut(): bool
    {
        return $this->hasFlag(self::PAPER_OUT);
    }

    public function coverOpen(): bool
    {
        return $this->hasFlag(self::COVER_OPEN);
    }

    public function fiscalMemoryLow(): bool
    {
        return $this->hasFlag(self::FISCAL_MEM_LOW);
    }

    public function fiscalMemoryFull(): bool
    {
        return $this->hasFlag(self::FISCAL_MEM_FULL);
    }

    public function receiptOpen(): bool
    {
        return $this->hasFlag(self::RECEIPT_OPEN);
    }

    public function nonFiscalOpen(): bool
    {
        return $this->hasFlag(self::NON_FISCAL_OPEN);
    }

    public function lastCommandOk(): bool
    {
        return $this->hasFlag(self::LAST_CMD_OK);
    }

    public function reserved(): bool
    {
        return $this->hasFlag(self::RESERVED);
    }

    public function isIdleAfterResetLikeState(): bool
    {
        return $this->value === self::RESERVED;
    }

    public function hasBlockingFiscalError(): bool
    {
        return $this->paperOut()
            || $this->coverOpen()
            || $this->fiscalMemoryFull();
    }

    public function toArray(): array
    {
        return [
            'raw' => $this->value,
            'paper_out' => $this->paperOut(),
            'cover_open' => $this->coverOpen(),
            'fiscal_memory_low' => $this->fiscalMemoryLow(),
            'fiscal_memory_full' => $this->fiscalMemoryFull(),
            'receipt_open' => $this->receiptOpen(),
            'non_fiscal_open' => $this->nonFiscalOpen(),
            'last_command_ok' => $this->lastCommandOk(),
            'reserved' => $this->reserved(),
            'idle_after_reset_like_state' => $this->isIdleAfterResetLikeState(),
            'blocking_fiscal_error' => $this->hasBlockingFiscalError(),
        ];
    }

    private function hasFlag(int $mask): bool
    {
        return ($this->value & $mask) === $mask;
    }
}
