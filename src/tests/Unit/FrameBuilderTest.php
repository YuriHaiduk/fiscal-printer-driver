<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\FiscalPrinter\Frame\BccCalculator;
use App\Services\FiscalPrinter\Frame\Bytes;
use App\Services\FiscalPrinter\Frame\FrameBuilder;
use App\Services\FiscalPrinter\Frame\SequenceGenerator;
use PHPUnit\Framework\TestCase;

final class FrameBuilderTest extends TestCase
{
    public function test_it_builds_get_status_frame(): void
    {
        $builder = new FrameBuilder(
            bccCalculator: new BccCalculator(),
            sequenceGenerator: new SequenceGenerator(start: 0x20),
        );

        $frame = $builder->build(command: 0x4A);

        $this->assertSame(
            '02 20 01 00 4A 03 68',
            Bytes::toHex($frame)
        );
    }

    public function test_it_increments_sequence_for_each_frame(): void
    {
        $builder = new FrameBuilder(
            bccCalculator: new BccCalculator(),
            sequenceGenerator: new SequenceGenerator(start: 0x20),
        );

        $firstFrame = $builder->build(command: 0x4A);
        $secondFrame = $builder->build(command: 0x4A);

        $this->assertSame('02 20 01 00 4A 03 68', Bytes::toHex($firstFrame));
        $this->assertSame('02 21 01 00 4A 03 69', Bytes::toHex($secondFrame));
    }

    public function test_it_wraps_sequence_after_0x7f(): void
    {
        $builder = new FrameBuilder(
            bccCalculator: new BccCalculator(),
            sequenceGenerator: new SequenceGenerator(start: 0x7F),
        );

        $firstFrame = $builder->build(command: 0x4A);
        $secondFrame = $builder->build(command: 0x4A);

        $this->assertSame('02 7F 01 00 4A 03 37', Bytes::toHex($firstFrame));
        $this->assertSame('02 20 01 00 4A 03 68', Bytes::toHex($secondFrame));
    }
}
