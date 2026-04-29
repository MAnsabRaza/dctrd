<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ExchangeRateService;
use App\Models\ExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExchangeRateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExchangeRateService();
    }

    /** @test */
    public function it_returns_one_for_same_currency()
    {
        $rate = $this->service->getRate('USD', 'USD');
        $this->assertEquals(1.0, $rate);
    }

    /** @test */
    public function it_converts_same_currency_correctly()
    {
        $result = $this->service->convert(100, 'USD', 'USD');
        $this->assertEquals(100, $result);
    }

    /** @test */
    public function it_converts_different_currencies()
    {
        // Create a test exchange rate
        ExchangeRate::create([
            'base_currency' => 'USD',
            'target_currency' => 'EUR',
            'rate' => 0.85,
            'provider' => 'test',
            'fetched_at' => now(),
        ]);

        $result = $this->service->convert(100, 'USD', 'EUR');
        $this->assertEquals(85.0, $result);
    }

    /** @test */
    public function it_returns_original_amount_when_rate_not_found()
    {
        $result = $this->service->convert(100, 'USD', 'XYZ');
        $this->assertEquals(100, $result);
    }

    /** @test */
    public function it_gets_latest_rate_from_database()
    {
        // Create multiple rates
        ExchangeRate::create([
            'base_currency' => 'USD',
            'target_currency' => 'EUR',
            'rate' => 0.85,
            'provider' => 'test',
            'fetched_at' => now()->subHour(),
        ]);

        ExchangeRate::create([
            'base_currency' => 'USD',
            'target_currency' => 'EUR',
            'rate' => 0.87,
            'provider' => 'test',
            'fetched_at' => now(),
        ]);

        $rate = $this->service->getRate('USD', 'EUR');
        $this->assertEquals(0.87, $rate);
    }

    /** @test */
    public function it_gets_supported_currencies()
    {
        $currencies = $this->service->getSupportedCurrencies();
        $this->assertIsArray($currencies);
        $this->assertContains('USD', $currencies);
        $this->assertContains('EUR', $currencies);
    }

    /** @test */
    public function it_checks_if_enabled()
    {
        $enabled = $this->service->isEnabled();
        $this->assertIsBool($enabled);
    }

    /** @test */
    public function it_gets_last_update_time()
    {
        ExchangeRate::create([
            'base_currency' => 'USD',
            'target_currency' => 'EUR',
            'rate' => 0.85,
            'provider' => 'test',
            'fetched_at' => now(),
        ]);

        $lastUpdate = $this->service->getLastUpdateTime();
        $this->assertNotNull($lastUpdate);
        $this->assertInstanceOf(\Carbon\Carbon::class, $lastUpdate);
    }
}
