<?php

use App\Http\Controllers\BoardingHouseController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\NotificationFeedController;
use Illuminate\Support\Facades\Route;


Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/notifications/feed', [NotificationFeedController::class, 'index'])->name(
        'notifications.feed',
    );
    Route::post('/booking/approve-reject', [
        BookingController::class,
        'approveRejectFromNotification',
    ])->name('booking.approve-reject');
});

Route::get('/recommended/show-all', [BoardingHouseController::class, 'showAll'])->name(
    'boarding-house.show-all',
);


Route::get('/find-kost', [BoardingHouseController::class, 'find'])->name('find-kos');
Route::get('/find-results', [BoardingHouseController::class, 'findResults'])->name(
    'find-kos.results',
);

Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/city/{slug}', [CityController::class, 'show'])->name('city.show');
Route::get('/cities', [CityController::class, 'showAll'])->name('city.show-all');

Route::get('/check-booking', [BookingController::class, 'check'])->name('check-booking');
Route::get('/kost/{slug}', [BoardingHouseController::class, 'show'])->name('kos.show');
Route::get('/kost/{slug}/rooms', [BoardingHouseController::class, 'rooms'])->name('kos.rooms');

Route::get('/kost/booking/{slug}', [BookingController::class, 'booking'])->name('booking');
Route::get('/kost/booking/{slug}/cust-information', [
    BookingController::class,
    'information',
])->name('booking.cust-information');
Route::post('/kost/booking/{slug}/information/save', [
    BookingController::class,
    'saveInformation',
])->name('booking.information.save');

Route::get('/kost/booking/{slug}/checkout', [BookingController::class, 'checkout'])->name(
    'booking.checkout',
);

Route::post('/kost/booking/{slug}/payment', [BookingController::class, 'payment'])->name(
    'booking.payment',
);

Route::get('/kost/booking/{slug}/waiting-approval', [
    BookingController::class,
    'waitingApproval',
])->name('booking.waiting-approval');

Route::get('/kost/booking/{slug}/pay-now', [
    BookingController::class,
    'payNowFromNotification',
])->name('booking.pay-now-from-notification');

Route::get('/booking-success', [BookingController::class, 'success'])->name('booking.success');

Route::get('/check-booking', [BookingController::class, 'check'])->name('check-booking');
Route::post('/check-booking', [BookingController::class, 'show'])->name('check-booking.show');

// Maps
use App\Http\Controllers\GeoProxyController;

Route::middleware('throttle:geocode')->group(function () {
    Route::get('/geocode', [GeoProxyController::class, 'search'])->name('geocode.search');

    Route::get('/geocode/search', [GeoProxyController::class, 'search']);
    Route::get('/geocode/reverse', [GeoProxyController::class, 'search']);
    
});

use App\Http\Controllers\TileProxyController;
Route::get('/tiles/{z}/{x}/{y}.png', [TileProxyController::class, 'tile']);


