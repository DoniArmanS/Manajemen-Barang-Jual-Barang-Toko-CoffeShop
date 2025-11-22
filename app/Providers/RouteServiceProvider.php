<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * URL default setelah login (boleh dipakai, boleh diabaikan).
     */
    public const HOME = '/home';

    /**
     * Daftarkan semua route aplikasi.
     */
    public function boot(): void
    {
        $this->routes(function () {
            // Semua route API → prefix "api/..." dan middleware "api"
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Semua route web biasa → pakai middleware "web"
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
