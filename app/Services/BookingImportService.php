<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingAvailability;
use App\Models\BookingCategory;
use App\Models\BookingImport;
use App\Models\BookingImportLog;
use App\Models\BookingOrder;
use App\Models\BookingOrderItem;
use App\Models\BookingRatePlan;
use App\Models\BookingResource;
use App\Models\BookingVariant;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Reader;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BookingImportService
{
    public function process(BookingImport $import): void
    {
        $errors = [];
        $successRows = 0;
        $failedRows = 0;
        $duplicateRows = 0;
        $processed = 0;

        try {
            $import->update(['status' => 'processing', 'started_at' => now()]);

            $records = $this->readRecords($import);

            $import->update(['total_rows' => $records->count()]);

            foreach ($records as $offset => $record) {
                $rowNumber = ((int) $offset) + 2;
                $processed++;

                try {
                    DB::transaction(function () use ($import, $record, $rowNumber, &$duplicateRows) {
                        $result = $this->importRow($import, $record, $rowNumber);

                        if ($result === 'duplicate') {
                            $duplicateRows++;
                        }
                    });

                    $successRows++;
                } catch (\Throwable $e) {
                    $failedRows++;
                    $errors[] = ['row' => $rowNumber, 'message' => $e->getMessage(), 'data' => $record];
                    $this->log($import, $rowNumber, 'error', 'failed', $e->getMessage(), $record);
                }

                if ($processed % 50 === 0) {
                    $import->update(['processed_rows' => $processed]);
                }
            }

            $import->update([
                'status' => 'completed',
                'processed_rows' => $processed,
                'success_rows' => $successRows,
                'failed_rows' => $failedRows,
                'duplicate_rows' => $duplicateRows,
                'errors' => $errors ?: null,
                'summary' => compact('processed', 'successRows', 'failedRows', 'duplicateRows'),
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $import->update([
                'status' => 'failed',
                'errors' => [['message' => $e->getMessage()]],
                'finished_at' => now(),
            ]);
        }
    }

    private function importRow(BookingImport $import, array $record, int $rowNumber): ?string
    {
        return match ($import->type) {
            'resources' => $this->importResource($import, $record, $rowNumber),
            'categories' => $this->importCategory($import, $record, $rowNumber),
            'variants' => $this->importVariant($import, $record, $rowNumber),
            'pricing' => $this->importPricing($import, $record, $rowNumber),
            'availability' => $this->importAvailability($import, $record, $rowNumber),
            'orders' => $this->importOrder($import, $record, $rowNumber),
            default => $this->importBooking($import, $record, $rowNumber),
        };
    }

    private function readRecords(BookingImport $import)
    {
        $path = Storage::disk('private')->path($import->file_path);
        $extension = strtolower(pathinfo($import->file_name, PATHINFO_EXTENSION));

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            $sheet = IOFactory::load($path)->getActiveSheet();
            $rows = collect($sheet->toArray(null, true, true, false));
            $headers = collect($rows->shift() ?? [])->map(fn($header) => trim((string) $header))->all();

            return $rows
                ->filter(fn($row) => collect($row)->filter(fn($value) => $value !== null && $value !== '')->isNotEmpty())
                ->map(fn($row) => array_combine($headers, array_pad($row, count($headers), null)));
        }

        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        return collect($csv->getRecords());
    }

    private function importBooking(BookingImport $import, array $record, int $rowNumber): ?string
    {
        $title = trim($record['title'] ?? '');
        if ($title === '') {
            throw new \InvalidArgumentException('Title is required.');
        }

        $slug = trim($record['slug'] ?? '') ?: Str::slug($title);
        $existing = Booking::withTrashed()->where('slug', $slug)->first();
        if ($existing) {
            $this->log($import, $rowNumber, 'warning', 'duplicate', 'Booking slug already exists.', ['slug' => $slug]);
            return 'duplicate';
        }

        $booking = Booking::create([
            'creator_id' => (int) ($record['creator_id'] ?? $import->user_id),
            'category_id' => !empty($record['category_id']) ? (int) $record['category_id'] : null,
            'title' => $title,
            'slug' => $slug,
            'booking_type' => trim($record['booking_type'] ?? 'service'),
            'sub_type' => trim($record['sub_type'] ?? '') ?: null,
            'description' => trim($record['description'] ?? ''),
            'price' => (float) ($record['price'] ?? 0),
            'currency' => strtoupper(trim($record['currency'] ?? config('exchange.base_currency', 'USD'))),
            'capacity' => (int) ($record['capacity'] ?? 1),
            'status' => trim($record['status'] ?? 'draft'),
        ]);

        $this->log($import, $rowNumber, 'info', 'created', 'Booking imported.', $record, Booking::class, $booking->id);
        return null;
    }

    private function importCategory(BookingImport $import, array $record, int $rowNumber): ?string
    {
        $title = trim($record['title'] ?? '');
        if ($title === '') {
            throw new \InvalidArgumentException('Category title is required.');
        }

        $slug = trim($record['slug'] ?? '') ?: Str::slug($title);
        $category = BookingCategory::firstOrCreate(['slug' => $slug], [
            'title' => $title,
            'parent_id' => !empty($record['parent_id']) ? (int) $record['parent_id'] : null,
            'status' => (bool) ($record['status'] ?? true),
            'order' => (int) ($record['order'] ?? 0),
        ]);

        $this->log($import, $rowNumber, 'info', $category->wasRecentlyCreated ? 'created' : 'duplicate', 'Category imported.', $record, BookingCategory::class, $category->id);
        return $category->wasRecentlyCreated ? null : 'duplicate';
    }

    private function importResource(BookingImport $import, array $record, int $rowNumber): ?string
    {
        $bookingId = $this->requiredBookingId($record);
        $name = trim($record['name'] ?? '');
        if ($name === '') {
            throw new \InvalidArgumentException('Resource name is required.');
        }

        $resource = BookingResource::updateOrCreate(['booking_id' => $bookingId, 'name' => $name], [
            'type' => trim($record['type'] ?? 'asset'),
            'description' => trim($record['description'] ?? '') ?: null,
            'capacity' => (int) ($record['capacity'] ?? 1),
            'extra_price' => (float) ($record['extra_price'] ?? $record['price_modifier'] ?? 0),
            'status' => (bool) ($record['status'] ?? true),
        ]);

        $this->log($import, $rowNumber, 'info', $resource->wasRecentlyCreated ? 'created' : 'updated', 'Resource imported.', $record, BookingResource::class, $resource->id);
        return null;
    }

    private function importVariant(BookingImport $import, array $record, int $rowNumber): ?string
    {
        $bookingId = $this->requiredBookingId($record);
        $title = trim($record['title'] ?? '');
        if ($title === '') {
            throw new \InvalidArgumentException('Variant title is required.');
        }

        $variant = BookingVariant::updateOrCreate(['booking_id' => $bookingId, 'name' => $title], [
            'options' => $this->decodeJson($record['options'] ?? '[]') ?: [],
            'price_modifier' => (float) ($record['price_modifier'] ?? $record['price'] ?? 0),
            'status' => (bool) ($record['status'] ?? true),
            'sort_order' => (int) ($record['sort_order'] ?? 0),
        ]);

        $this->log($import, $rowNumber, 'info', $variant->wasRecentlyCreated ? 'created' : 'updated', 'Variant imported.', $record, BookingVariant::class, $variant->id);
        return null;
    }

    private function importPricing(BookingImport $import, array $record, int $rowNumber): ?string
    {
        $bookingId = $this->requiredBookingId($record);

        $ratePlan = BookingRatePlan::create([
            'booking_id' => $bookingId,
            'name' => trim($record['name'] ?? $record['title'] ?? 'Imported rate'),
            'type' => trim($record['type'] ?? 'base'),
            'price' => (float) ($record['price'] ?? $record['adjustment_value'] ?? 0),
            'price_unit' => trim($record['price_unit'] ?? 'day'),
            'calculation_type' => trim($record['calculation_type'] ?? $record['adjustment_type'] ?? 'fixed'),
            'conditions' => $this->decodeJson($record['conditions'] ?? null),
            'priority' => (int) ($record['priority'] ?? 0),
            'status' => (bool) ($record['status'] ?? true),
        ]);

        $this->log($import, $rowNumber, 'info', 'created', 'Pricing row imported.', $record, BookingRatePlan::class, $ratePlan->id);
        return null;
    }

    private function importAvailability(BookingImport $import, array $record, int $rowNumber): ?string
    {
        $bookingId = $this->requiredBookingId($record);

        $availability = BookingAvailability::updateOrCreate([
            'booking_id' => $bookingId,
            'resource_id' => !empty($record['resource_id']) ? (int) $record['resource_id'] : null,
            'date' => $record['date'] ?? null,
        ], [
            'is_available' => (bool) ($record['is_available'] ?? true),
            'price_override' => isset($record['price']) ? (float) $record['price'] : null,
            'slots_available' => isset($record['slots']) ? (int) $record['slots'] : null,
            'close_reason' => trim($record['close_reason'] ?? '') ?: null,
        ]);

        $this->log($import, $rowNumber, 'info', $availability->wasRecentlyCreated ? 'created' : 'updated', 'Availability imported.', $record, BookingAvailability::class, $availability->id);
        return null;
    }

    private function importOrder(BookingImport $import, array $record, int $rowNumber): ?string
    {
        $bookingId = $this->requiredBookingId($record);
        $userId = (int) ($record['user_id'] ?? $import->user_id);

        if (!User::where('id', $userId)->exists()) {
            throw new \InvalidArgumentException('Valid user_id is required.');
        }

        $quantity = max(1, (int) ($record['quantity'] ?? 1));
        $unitPrice = (float) ($record['unit_price'] ?? 0);
        $totalPrice = round($quantity * $unitPrice, 2);
        $orderNumber = trim($record['order_number'] ?? '') ?: 'IMPORT-' . Str::upper(Str::random(10));

        if (BookingOrder::where('order_number', $orderNumber)->exists()) {
            $this->log($import, $rowNumber, 'warning', 'duplicate', 'Order number already exists.', ['order_number' => $orderNumber]);
            return 'duplicate';
        }

        $order = BookingOrder::create([
            'order_number' => $orderNumber,
            'user_id' => $userId,
            'subtotal' => $totalPrice,
            'discount_amount' => (float) ($record['discount_amount'] ?? 0),
            'tax_amount' => (float) ($record['tax_amount'] ?? 0),
            'total' => max(0, $totalPrice - (float) ($record['discount_amount'] ?? 0) + (float) ($record['tax_amount'] ?? 0)),
            'currency' => strtoupper(trim($record['currency'] ?? config('exchange.base_currency', 'USD'))),
            'status' => trim($record['status'] ?? 'pending'),
            'payment_status' => trim($record['payment_status'] ?? 'unpaid'),
            'notes' => trim($record['notes'] ?? '') ?: null,
        ]);

        BookingOrderItem::create([
            'order_id' => $order->id,
            'item_type' => 'booking',
            'booking_id' => $bookingId,
            'resource_id' => !empty($record['resource_id']) ? (int) $record['resource_id'] : null,
            'booking_date' => trim($record['booking_date'] ?? '') ?: null,
            'start_time' => trim($record['start_time'] ?? '') ?: null,
            'end_time' => trim($record['end_time'] ?? '') ?: null,
            'quantity' => $quantity,
            'persons' => max(1, (int) ($record['persons'] ?? 1)),
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'status' => trim($record['item_status'] ?? $record['status'] ?? 'pending'),
        ]);

        $this->log($import, $rowNumber, 'info', 'created', 'Order imported.', $record, BookingOrder::class, $order->id);
        return null;
    }

    private function requiredBookingId(array $record): int
    {
        $bookingId = !empty($record['booking_id']) ? (int) $record['booking_id'] : 0;
        if ($bookingId < 1 || !Booking::where('id', $bookingId)->exists()) {
            throw new \InvalidArgumentException('Valid booking_id is required.');
        }

        return $bookingId;
    }

    private function log(BookingImport $import, ?int $rowNumber, string $level, string $action, string $message, array $payload = [], ?string $modelType = null, ?int $modelId = null): void
    {
        BookingImportLog::create([
            'import_id' => $import->id,
            'row_number' => $rowNumber,
            'level' => $level,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'message' => $message,
            'payload' => $payload,
        ]);
    }

    private function decodeJson($value): ?array
    {
        if (empty($value)) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : null;
    }
}
