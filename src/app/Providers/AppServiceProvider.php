<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FiscalPrinter\Contracts\FiscalPrinterDriver;
use App\Services\FiscalPrinter\Contracts\SerialConnection;
use App\Services\FiscalPrinter\FpMoldovaM1Driver;
use App\Services\FiscalPrinter\Transport\MockSerialConnection;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SerialConnection::class, MockSerialConnection::class);
        $this->app->bind(FiscalPrinterDriver::class, FpMoldovaM1Driver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
