<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

class UpdateExchangeRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update exchange rates from API providers';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ExchangeRateService $service)
    {
        $this->info('🔄 Updating exchange rates...');
        
        if (!$service->isEnabled()) {
            $this->warn('⚠️  Exchange rate updates are disabled in configuration');
            return 1;
        }

        $startTime = microtime(true);
        
        if ($service->updateRates()) {
            $duration = round(microtime(true) - $startTime, 2);
            $lastUpdate = $service->getLastUpdateTime();
            
            $this->info('✅ Exchange rates updated successfully!');
            $this->info("⏱️  Duration: {$duration} seconds");
            $this->info("📅 Last update: {$lastUpdate->format('Y-m-d H:i:s')}");
            
            return 0;
        }

        $this->error('❌ Failed to update exchange rates. Check logs for details.');
        return 1;
    }
}
