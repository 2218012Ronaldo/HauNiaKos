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

    /**
     * Convert an amount to USD, auto-detecting whether the input is IDR or already USD.
     * - If the amount is >= 1000 we assume it's an IDR value and convert by rate.
     * - If the amount is smaller (e.g. 25.71) we assume it's already USD and return as float.
     * This helps avoid converting already-USD values stored in the DB or small sample data.
     *
     * @param mixed $amount
     * @return float
     */
    public function convertToUsdNormalized(mixed $amount): float
    {
        $rate = config('currency.rates.IDR', 17500);

        // Normalize numeric string to float/int
        if (is_string($amount)) {
            // remove non-numeric characters except dot and comma
            $clean = preg_replace('/[^0-9.,-]/', '', $amount);
            // Replace comma with dot if decimal style
            $clean = str_replace(',', '.', $clean);
            $value = is_numeric($clean) ? (float) $clean : 0.0;
        } elseif (is_int($amount) || is_float($amount)) {
            $value = $amount;
        } else {
            $value = 0.0;
        }

        // If value looks like IDR (large number), convert to USD
        if ($value >= 1000) {
            return round($value / $rate, 2);
        }

        // Otherwise assume already USD
        return round((float) $value, 2);
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