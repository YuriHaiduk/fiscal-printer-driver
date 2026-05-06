<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\FiscalPrinter\Exceptions\InvalidBccException;
use App\Services\FiscalPrinter\Exceptions\InvalidFrameException;
use App\Services\FiscalPrinter\Frame\BccCalculator;
use App\Services\FiscalPrinter\Frame\Bytes;
use App\Services\FiscalPrinter\Frame\FrameParser;
use PHPUnit\Framework\TestCase;

final class FrameParserTest extends TestCase
{
    public function test_it_parses_valid_get_status_response_frame(): void
    {
        $parser = new FrameParser(new BccCalculator());

        // STX SEQ LEN CMD DATA(status byte) ETX BCC
        // DATA = 0xC0 means RESERVED + LAST_CMD_OK
        $frame = Bytes::fromHex('02 20 02 00 4A C0 03 AB');

        $parsed = $parser->parse($frame);

        $this->assertSame(0x20, $parsed->sequence);
        $this->assertSame(2, $parsed->length);
        $this->assertSame(0x4A, $parsed->command);
        $this->assertSame('C0', Bytes::toHex($parsed->data));
        $this->assertSame(0xC0, $parsed->statusByte());
        $this->assertSame(0xAB, $parsed->bcc);
    }

    public function test_it_rejects_frame_with_invalid_stx(): void
    {
        $parser = new FrameParser(new BccCalculator());

        $this->expectException(InvalidFrameException::class);

        $parser->parse(Bytes::fromHex('01 20 02 00 4A C0 03 AB'));
    }

    public function test_it_rejects_frame_with_invalid_etx(): void
    {
        $parser = new FrameParser(new BccCalculator());

        $this->expectException(InvalidFrameException::class);

        $parser->parse(Bytes::fromHex('02 20 02 00 4A C0 04 AB'));
    }

    public function test_it_rejects_frame_with_invalid_bcc(): void
    {
        $parser = new FrameParser(new BccCalculator());

        $this->expectException(InvalidBccException::class);

        $parser->parse(Bytes::fromHex('02 20 02 00 4A C0 03 00'));
    }

    public function test_it_rejects_frame_when_len_does_not_match_payload_size(): void
    {
        $parser = new FrameParser(new BccCalculator());

        // LEN says 3 bytes CMD+DATA, but actual CMD+DATA is only 2 bytes before ETX.
        // BCC is calculated for the actual bytes: 20 03 00 4A C0 03 = AA
        $this->expectException(InvalidFrameException::class);

        $parser->parse(Bytes::fromHex('02 20 03 00 4A C0 03 AA'));
    }
}
