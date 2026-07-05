<?php

use App\Services\CurrencyService;

if (! function_exists('formatUsd')) {
    function formatUsd(float $amountInUsd): string
    {
        $currencyService = app(CurrencyService::class);

        return $currencyService->formatUsd($amountInUsd);
    }
}