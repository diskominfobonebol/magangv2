<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        spl_autoload_register(function ($class) {
            if (str_starts_with($class, 'SimpleSoftwareIO\\QrCode\\')) {
                $path = base_path('vendor/simplesoftwareio/simple-qrcode/src/' . str_replace('\\', '/', substr($class, strlen('SimpleSoftwareIO\\QrCode\\'))) . '.php');
                if (file_exists($path)) {
                    require_once $path;
                }
            }
            if (str_starts_with($class, 'BaconQrCode\\')) {
                $path = base_path('vendor/bacon/bacon-qr-code/src/' . str_replace('\\', '/', substr($class, strlen('BaconQrCode\\'))) . '.php');
                if (file_exists($path)) {
                    require_once $path;
                }
            }
            if (str_starts_with($class, 'DASPRiD\\Enum\\')) {
                $path = base_path('vendor/dasprid/enum/src/' . str_replace('\\', '/', substr($class, strlen('DASPRiD\\Enum\\'))) . '.php');
                if (file_exists($path)) {
                    require_once $path;
                }
            }
        });

        if (class_exists(\SimpleSoftwareIO\QrCode\QrCodeServiceProvider::class)) {
            $this->app->register(\SimpleSoftwareIO\QrCode\QrCodeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.custom');
    }
}
