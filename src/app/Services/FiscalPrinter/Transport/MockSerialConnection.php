<?php

declare(strict_types=1);

namespace App\Services\FiscalPrinter\Transport;

use App\Services\FiscalPrinter\Contracts\SerialConnection;
use RuntimeException;

final class MockSerialConnection implements SerialConnection
{
    /**
     * @var list<string>
     */
    private array $writtenFrames = [];

    /**
     * @var list<string>
     */
    private array $responses = [];

    public function write(string $bytes): void
    {
        $this->writtenFrames[] = $bytes;
    }

    public function read(): string
    {
        $response = array_shift($this->responses);

        if ($response === null) {
            throw new RuntimeException('No mock response available.');
        }

        return $response;
    }

    public function pushResponse(string $bytes): void
    {
        $this->responses[] = $bytes;
    }

    /**
     * @return list<string>
     */
    public function writtenFrames(): array
    {
        return $this->writtenFrames;
    }

    public function clear(): void
    {
        $this->writtenFrames = [];
        $this->responses = [];
    }
}
