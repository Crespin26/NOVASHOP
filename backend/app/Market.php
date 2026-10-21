<?php

namespace App;

use Illuminate\Support\Carbon;

class Market
{
    public function convert(int $amount, string $currency): int
    {
        $base = config('commerce.currency');
        abort_unless(in_array($currency, config('markets.currencies'), true), 422, 'Unknown currency.');
        if ($base === $currency) {
            return $amount;
        }
        $rate = config('commerce.exchange_rates.'.$currency);
        abort_unless(is_array($rate) && isset($rate['numerator'],$rate['denominator'],$rate['updated_at']), 422, 'Currency is not configured for checkout.');
        abort_unless(is_int($rate['numerator']) && is_int($rate['denominator']) && $rate['numerator'] > 0 && $rate['numerator'] <= 1000000000 && $rate['denominator'] > 0 && $rate['denominator'] <= 1000000000, 503, 'Invalid exchange rate configuration.');
        $updated = Carbon::parse($rate['updated_at']);
        abort_unless($updated->between(now()->subDay(), now()), 503, 'Exchange rate expired.');
        $formatter = new \NumberFormatter('en', \NumberFormatter::CURRENCY);
        $formatter->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $currency);
        $targetDigits = $formatter->getAttribute(\NumberFormatter::FRACTION_DIGITS);
        $formatter->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $base);
        $baseDigits = $formatter->getAttribute(\NumberFormatter::FRACTION_DIGITS);
        $multiplier = $rate['numerator'] * (10 ** $targetDigits);
        $denominator = $rate['denominator'] * (10 ** $baseDigits);
        abort_if($amount > intdiv(PHP_INT_MAX, $multiplier), 422, 'Converted amount exceeds supported limits.');
        $value = $amount * $multiplier;
        $whole = intdiv($value, $denominator);

        return $whole + (($value % $denominator) * 2 >= $denominator ? 1 : 0);
    }
}
