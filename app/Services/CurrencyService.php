<?php

namespace App\Services;

class CurrencyService
{
    public function convertToIdr(float $amountInUsd): int
    {
        $rate = config('currency.rates.IDR', 17500);

        return (int) round($amountInUsd * $rate);
    }

    public function convertToUsd(int $amountInIdr): float
    {
        $rate = config('currency.rates.IDR', 17500);

        return round($amountInIdr / $rate, 2);
    }

    public function formatUsd(float $amount): string
    {
        $symbol = config('currency.symbols.USD', '$');
        $decimals = config('currency.decimals.USD', 2);

        return $symbol.number_format($amount, $decimals, '.', ',');
    }

    public function formatIdr(int $amount): string
    {
        $symbol = config('currency.symbols.IDR', 'Rp');
        $decimals = config('currency.decimals.IDR', 0);

        return $symbol.number_format($amount, $decimals, ',', '.');
    }
}