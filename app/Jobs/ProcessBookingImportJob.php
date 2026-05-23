<?php

namespace App\Jobs;

use App\Models\BookingImport;
use App\Services\BookingImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBookingImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 900;

    public function __construct(public int $importId)
    {
        $this->onQueue(config('queue.booking_imports_queue', 'imports'));
    }

    public function handle(BookingImportService $service): void
    {
        $import = BookingImport::findOrFail($this->importId);
        $service->process($import);
    }
}
