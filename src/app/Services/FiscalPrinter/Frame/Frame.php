<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter\Frame;

final readonly class Frame
{
    public function __construct(
        public int $sequence,
        public int $length,
        public int $command,
        public string $data,
        public int $bcc,
    ) {
    }

    public function statusByte(): int
    {
        if ($this->data === '') {
            return 0;
        }

        return ord($this->data[0]);
    }

    public function payload(): string
    {
        return $this->data;
    }
}
