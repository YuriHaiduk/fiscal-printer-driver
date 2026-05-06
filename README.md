# Fiscal Printer Driver

Minimal Laravel/PHP implementation of the FP-Moldova M1 fiscal printer protocol subset.

## Requirements

- Docker
- Docker Compose

## Installation

Build the container:

    docker compose build

Install dependencies:

    docker compose run --rm app composer install

## Run tests

    docker compose run --rm app php artisan test

## Run demo sale flow

    docker compose run --rm app php artisan fp-moldova:demo-sale

Expected output:

    Demo sale flow completed.

    Frames written to mock serial connection:

    1. 02 20 0A 00 30 31 3B 30 30 30 30 3B 30 31 03 29
    2. 02 21 13 00 31 42 72 65 61 64 09 41 09 35 2E 30 30 09 32 2E 30 30 30 03 2F
    3. 02 22 08 00 35 30 3B 31 30 2E 30 30 03 38
    4. 02 23 01 00 38 03 19

## Implemented subset

- Frame building
- Response parsing
- BCC validation
- Status byte decoding
- GetStatus
- Sale flow: OpenFiscalReceipt, RegisterSale, Payment, CloseFiscalReceipt
- Printer error handling
- Mock serial connection for running without real hardware

## Notes

The project uses Laravel as a CLI/test runner and service container.

The project does not require a database, web server, or real printer hardware.

BCC calculation was written manually and marked in code as required by the task.
