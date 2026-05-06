<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter\Frame;

use App\Services\FiscalPrinter\Exceptions\InvalidFrameException;

final readonly class FrameBuilder
{
    private const STX = 0x02;
    private const ETX = 0x03;
    private const MAX_TOTAL_FRAME_SIZE = 512;
    private const MAX_DATA_SIZE = 506;

    public function __construct(
        private BccCalculator $bccCalculator,
        private SequenceGenerator $sequenceGenerator,
    ) {
    }

    public function build(int $command, string $payload = ''): string
    {
        if ($command < 0x00 || $command > 0xFF) {
            throw new InvalidFrameException('Command must be a single byte.');
        }

        if (strlen($payload) > self::MAX_DATA_SIZE) {
            throw new InvalidFrameException('Payload is too large.');
        }

        $sequence = $this->sequenceGenerator->next();

        $commandAndData = chr($command) . $payload;

        $length = strlen($commandAndData);

        $lengthBytes = pack('v', $length); // unsigned short, little-endian

        $frameWithoutStxAndBcc =
            chr($sequence)
            . $lengthBytes
            . $commandAndData
            . chr(self::ETX);

        $bcc = $this->bccCalculator->calculate($frameWithoutStxAndBcc);

        $frame = chr(self::STX) . $frameWithoutStxAndBcc . chr($bcc);

        if (strlen($frame) > self::MAX_TOTAL_FRAME_SIZE) {
            throw new InvalidFrameException('Total frame size exceeds 512 bytes.');
        }

        return $frame;
    }
}
