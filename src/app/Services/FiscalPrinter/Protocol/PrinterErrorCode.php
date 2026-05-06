<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter\Protocol;

enum PrinterErrorCode: string
{
    case UnknownCommand = 'E1';
    case CommandSequenceViolation = 'E2';
    case InvalidArgumentFormat = 'E3';
    case ArgumentOutOfRange = 'E4';
    case HardwareError = 'E5';
    case FiscalMemoryFullOrWriteFailure = 'E6';
    case OperatorAuthenticationFailed = 'E7';
    case SubtotalPaymentMismatch = 'E8';

    public static function fromPayload(string $payload): self
    {
        return match ($payload) {
            'E1' => self::UnknownCommand,
            'E2' => self::CommandSequenceViolation,
            'E3' => self::InvalidArgumentFormat,
            'E4' => self::ArgumentOutOfRange,
            'E5' => self::HardwareError,
            'E6' => self::FiscalMemoryFullOrWriteFailure,
            'E7' => self::OperatorAuthenticationFailed,
            'E8' => self::SubtotalPaymentMismatch,
            default => throw new \InvalidArgumentException(sprintf(
                'Unknown printer error code: %s',
                $payload
            )),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::UnknownCommand => 'Unknown command',
            self::CommandSequenceViolation => 'Command sequence violation',
            self::InvalidArgumentFormat => 'Invalid argument format',
            self::ArgumentOutOfRange => 'Argument out of range',
            self::HardwareError => 'Hardware error',
            self::FiscalMemoryFullOrWriteFailure => 'Fiscal memory full or write failure',
            self::OperatorAuthenticationFailed => 'Operator authentication failed',
            self::SubtotalPaymentMismatch => 'Subtotal/payment mismatch',
        };
    }
}
