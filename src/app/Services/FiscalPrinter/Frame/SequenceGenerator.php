<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter\Frame;

final class SequenceGenerator
{
    private const MIN_SEQUENCE = 0x20;
    private const MAX_SEQUENCE = 0x7F;

    private int $current;

    public function __construct(int $start = self::MIN_SEQUENCE)
    {
        if ($start < self::MIN_SEQUENCE || $start > self::MAX_SEQUENCE) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Sequence must be between 0x%02X and 0x%02X.',
                    self::MIN_SEQUENCE,
                    self::MAX_SEQUENCE
                )
            );
        }

        $this->current = $start;
    }

    public function next(): int
    {
        $sequence = $this->current;

        $this->current++;

        if ($this->current > self::MAX_SEQUENCE) {
            $this->current = self::MIN_SEQUENCE;
        }

        return $sequence;
    }
}
