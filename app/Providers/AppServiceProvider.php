<?php

namespace App\Providers;

use App\Auth\Responses\LoginResponse;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Cache\RateLimiting\Limit;
// ➕ Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {

    $this->app->bind(
        \Filament\Auth\Http\Responses\Contracts\LoginResponse::class,
         LoginResponse::class,
    );

     $this->app->bind(
        \Filament\Auth\Http\Responses\Contracts\LogoutResponse::class,
        \App\Auth\Responses\LogoutResponse::class,
    );

     $this->app->bind(
        \Filament\Auth\Http\Responses\Contracts\RegistrationResponse::class,
        \App\Auth\Responses\RegistrationResponse::class,
    );

    Livewire::component('edit_profile', \App\Livewire\Profile\EditProfile::class);

        // ✅ PUNYA KAMU (biarkan)
        RateLimiter::for('geocode', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip() ?: 'global');
        });

        // ➕ TAMBAHKAN INI
        FilamentAsset::register([
            Css::make('leaflet-css', 'https://unpkg.com/leaflet/dist/leaflet.css'),
            Js::make('leaflet-js', 'https://unpkg.com/leaflet/dist/leaflet.js'),
            Js::make('picker-js', asset('js/map/picker.js')),
        ]);

        if (request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}
