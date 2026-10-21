<?php

namespace Tests\Feature;

use App\Market;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MarketTest extends TestCase
{
    public function test_currency_conversion_uses_minor_units_and_fresh_server_rates(): void
    {
        config(['commerce.exchange_rates' => ['XOF' => ['numerator' => 655957, 'denominator' => 1000, 'updated_at' => now()->toISOString()], 'USD' => ['numerator' => 11225, 'denominator' => 10000, 'updated_at' => now()->toISOString()]]]);
        $market = new Market;
        $this->assertSame(58380, $market->convert(8900, 'XOF'));
        $this->assertSame(9990, $market->convert(8900, 'USD'));
        $this->assertSame(8900, $market->convert(8900, 'EUR'));
        $this->assertContains('JP', config('markets.countries'));
        $this->assertContains('JPY', config('markets.currencies'));
    }

    public function test_unconfigured_currencies_are_not_offered_as_payable(): void
    {
        $this->expectException(HttpException::class);
        app(Market::class)->convert(100, 'JPY');
    }
}
