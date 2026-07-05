<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Currency Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains settings for currency conversion
    | and display in the application.
    |
    */

    // Default display currency
    'default' => 'USD',

    // Exchange rates (base: USD)
    'rates' => [
        'IDR' => env('USD_TO_IDR_RATE', 17500), // 1 USD = 16,200 IDR (current rate 2026)
    ],

    // Currency symbols
    'symbols' => [
        'USD' => '$',
        'IDR' => 'Rp',
    ],

    // Number of decimal places for display
    'decimals' => [
        'USD' => 2,
        'IDR' => 0,
    ],
];