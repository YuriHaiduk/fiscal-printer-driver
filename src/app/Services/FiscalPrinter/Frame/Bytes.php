<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter\Frame;

use InvalidArgumentException;

final class Bytes
{
    public static function toHex(string $bytes): string
    {
        if ($bytes === '') {
            return '';
        }

        return strtoupper(implode(' ', str_split(bin2hex($bytes), 2)));
    }

    public static function fromHex(string $hex): string
    {
        $cleanHex = preg_replace('/\s+/', '', $hex);

        if ($cleanHex === null || $cleanHex === '') {
            return '';
        }

        $bytes = hex2bin($cleanHex);

        if ($bytes === false) {
            throw new InvalidArgumentException('Invalid hex string.');
        }

        return $bytes;
    }
}
