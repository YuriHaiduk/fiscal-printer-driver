<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter\Frame;

use App\Services\FiscalPrinter\Exceptions\InvalidBccException;
use App\Services\FiscalPrinter\Exceptions\InvalidFrameException;

final readonly class FrameParser
{
    private const STX = 0x02;
    private const ETX = 0x03;
    private const MIN_TOTAL_FRAME_SIZE = 7;
    private const MAX_TOTAL_FRAME_SIZE = 512;

    public function __construct(
        private BccCalculator $bccCalculator,
    ) {
    }

    public function parse(string $rawFrame): Frame
    {
        $frameSize = strlen($rawFrame);

        if ($frameSize < self::MIN_TOTAL_FRAME_SIZE) {
            throw new InvalidFrameException('Frame is too short.');
        }

        if ($frameSize > self::MAX_TOTAL_FRAME_SIZE) {
            throw new InvalidFrameException('Frame exceeds maximum size of 512 bytes.');
        }

        if (ord($rawFrame[0]) !== self::STX) {
            throw new InvalidFrameException('Invalid STX byte.');
        }

        $bccOffset = $frameSize - 1;
        $etxOffset = $frameSize - 2;

        if (ord($rawFrame[$etxOffset]) !== self::ETX) {
            throw new InvalidFrameException('Invalid ETX byte.');
        }

        $receivedBcc = ord($rawFrame[$bccOffset]);

        // BCC is XOR of all bytes from SEQ through ETX inclusive.
        // STX and BCC itself are not included.
        $bccBytes = substr($rawFrame, 1, $frameSize - 2);
        $calculatedBcc = $this->bccCalculator->calculate($bccBytes);

        if ($receivedBcc !== $calculatedBcc) {
            throw new InvalidBccException(
                sprintf(
                    'Invalid BCC. Expected %02X, received %02X.',
                    $calculatedBcc,
                    $receivedBcc
                )
            );
        }

        $sequence = ord($rawFrame[1]);

        $lengthBytes = substr($rawFrame, 2, 2);
        $length = unpack('v', $lengthBytes)[1];

        if ($length < 1 || $length > 0x01F4) {
            throw new InvalidFrameException('Invalid LEN value.');
        }

        $commandAndData = substr($rawFrame, 4, $length);

        if (strlen($commandAndData) !== $length) {
            throw new InvalidFrameException('LEN does not match actual CMD + DATA size.');
        }

        $expectedEtxOffset = 4 + $length;

        if ($expectedEtxOffset !== $etxOffset) {
            throw new InvalidFrameException('ETX position does not match LEN.');
        }

        $command = ord($commandAndData[0]);
        $data = substr($commandAndData, 1);

        return new Frame(
            sequence: $sequence,
            length: $length,
            command: $command,
            data: $data,
            bcc: $receivedBcc,
        );
    }
}
