<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter\Frame;

final class BccCalculator
{
    public function calculate(string $bytes): int
    {
        // Written manually, without AI
        $bcc = 0;

        for ($i = 0; $i < strlen($bytes); $i++) {
            $bcc ^= ord($bytes[$i]);
        }

        return $bcc;
    }
}
