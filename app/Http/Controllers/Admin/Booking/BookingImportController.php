<?php
namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingImport;
use App\Models\BookingOrder;
use App\Models\BookingOrderItem;
use App\Jobs\ProcessBookingImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class BookingImportController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_imports');

        removeContentLocale();

        $imports = BookingImport::with('user')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('admin.booking.import', [
            'pageTitle' => 'Booking Imports',
            'imports' => $imports,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_imports_create');

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
            'type' => 'required|in:bookings,resources,categories,variants,pricing,availability,orders',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('booking_imports', 'private');

        $import = BookingImport::create([
            'user_id' => Auth::id(),
            'file_path' => $filePath,
            'file_name' => $fileName,
            'type' => $request->type,
            'status' => 'pending',
        ]);

        ProcessBookingImportJob::dispatch($import->id);

        return redirect(getAdminPanelUrl('/booking/import'))
            ->with('success', 'Import queued successfully. You can track progress from the import logs.');
    }

    public function show($id)
    {
        $this->authorize('admin_booking_imports');

        $import = BookingImport::with('user')->findOrFail($id);

        $imports = BookingImport::with('user')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('admin.booking.import', [
            'pageTitle' => 'Import Details #' . $import->id,
            'imports' => $imports,
            'import' => $import,   // yeh set hoga to detail tab active ho ga
        ]);
    }
    public function delete($id)
    {
        $this->authorize('admin_booking_imports_delete');

        $import = BookingImport::findOrFail($id);

        // Delete the file from storage
        if (Storage::disk('private')->exists($import->file_path)) {
            Storage::disk('private')->delete($import->file_path);
        }

        $import->delete();

        return redirect(getAdminPanelUrl('/booking/import'))
            ->with('success', 'Import record deleted successfully.');
    }

    public function downloadSample()
    {
        $this->authorize('admin_booking_imports');

        $type = request()->get('type', 'bookings');

        if ($type === 'orders') {
            $headers = ['order_number', 'user_id', 'booking_id', 'booking_date', 'start_time', 'end_time', 'quantity', 'persons', 'unit_price', 'currency', 'status', 'payment_status'];
            $rows = [
                ['ORDER-1001', '2', '1', now()->addDay()->toDateString(), '09:00', '10:00', '1', '1', '100.00', 'USD', 'pending', 'unpaid'],
            ];
        } elseif ($type === 'resources') {
            $headers = ['booking_id', 'name', 'type', 'description', 'capacity', 'extra_price', 'status'];
            $rows = [['1', 'Room 101', 'room', 'King room', '2', '25.00', '1']];
        } elseif ($type === 'availability') {
            $headers = ['booking_id', 'resource_id', 'date', 'is_available', 'slots', 'price', 'close_reason'];
            $rows = [['1', '1', now()->addDay()->toDateString(), '1', '3', '120.00', '']];
        } elseif ($type === 'pricing') {
            $headers = ['booking_id', 'name', 'type', 'price', 'price_unit', 'calculation_type', 'conditions', 'priority', 'status'];
            $rows = [['1', 'Weekend', 'dow', '150.00', 'night', 'fixed', '{"days_of_week":[5,6]}', '10', '1']];
        } elseif ($type === 'variants') {
            $headers = ['booking_id', 'title', 'options', 'price_modifier', 'status'];
            $rows = [['1', 'Language', '["English","Arabic"]', '0', '1']];
        } elseif ($type === 'categories') {
            $headers = ['title', 'slug', 'parent_id', 'order', 'status'];
            $rows = [['Healthcare', 'healthcare', '', '0', '1']];
        } else {
            $headers = ['title', 'slug', 'creator_id', 'category_id', 'booking_type', 'sub_type', 'description', 'price', 'currency', 'capacity', 'status'];
            $rows = [
                ['Sample Booking 1', 'sample-booking-1', '2', '', 'service', 'consulting', 'Description here', '100.00', 'USD', '10', 'published'],
            ];
        }

        $csvContent = implode(',', $headers) . "\n";
        foreach ($rows as $row) {
            $csvContent .= implode(',', $row) . "\n";
        }

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="booking_import_sample.csv"',
        ]);
    }

    // ─────────────────────────────────────────────
    // Private helper
    // ─────────────────────────────────────────────

    private function processImport(BookingImport $import): void
    {
        try {
            $import->update(['status' => 'processing']);

            $fullPath = Storage::disk('private')->path($import->file_path);
            $csv = Reader::createFromPath($fullPath, 'r');
            $csv->setHeaderOffset(0);

            $records = collect($csv->getRecords());
            $totalRows = $records->count();

            $import->update(['total_rows' => $totalRows]);

            $errors = [];
            $successRows = 0;
            $failedRows = 0;
            $processed = 0;

            foreach ($records as $offset => $record) {
                $processed++;
                $rowNum = $offset + 2; // +2: 1-based + header row

                try {
                    if ($import->type === 'orders') {
                        $this->importOrderRow($record, $import);
                    } else {
                        $this->importBookingRow($record, $import);
                    }

                    $successRows++;

                } catch (\Throwable $e) {
                    $failedRows++;
                    $errors[] = [
                        'row' => $rowNum,
                        'data' => $record,
                        'message' => $e->getMessage(),
                    ];
                }

                // Update progress every 50 rows
                if ($processed % 50 === 0) {
                    $import->update(['processed_rows' => $processed]);
                }
            }

            $import->update([
                'status' => 'completed',
                'processed_rows' => $processed,
                'success_rows' => $successRows,
                'failed_rows' => $failedRows,
                'errors' => !empty($errors) ? $errors : null,
            ]);

        } catch (\Throwable $e) {
            $import->update([
                'status' => 'failed',
                'errors' => [['message' => $e->getMessage()]],
            ]);
        }
    }

    private function importBookingRow(array $record, BookingImport $import): void
    {
        $title = trim($record['title'] ?? '');

        if (empty($title)) {
            throw new \Exception('Title is required.');
        }

        $booking = Booking::create([
            'creator_id' => !empty($record['creator_id']) ? (int) $record['creator_id'] : $import->user_id,
            'category_id' => !empty($record['category_id']) ? (int) $record['category_id'] : null,
            'title' => $title,
            'slug' => \Str::slug($title) . '-' . uniqid(),
            'booking_type' => trim($record['booking_type'] ?? 'standard'),
            'description' => trim($record['description'] ?? ''),
            'price' => is_numeric($record['price'] ?? null) ? (float) $record['price'] : 0,
            'capacity' => is_numeric($record['capacity'] ?? null) ? (int) $record['capacity'] : 1,
            'status' => isset($record['status']) && (int) $record['status'] === 1 ? 'published' : 'inactive',
        ]);

        $notifyOptions = [
            '[c.title]' => $booking->title,
            '[item_title]' => $booking->title,
            '[u.name]' => optional($import->user)->full_name,
        ];

        sendNotification('booking_created', $notifyOptions, 1);
    }

    private function importOrderRow(array $record, BookingImport $import): void
    {
        $bookingId = !empty($record['booking_id']) ? (int) $record['booking_id'] : null;

        if (empty($bookingId) || !Booking::where('id', $bookingId)->exists()) {
            throw new \Exception('Valid booking_id is required.');
        }

        $userId = !empty($record['user_id']) ? (int) $record['user_id'] : $import->user_id;
        if (!\App\User::where('id', $userId)->exists()) {
            throw new \Exception('Valid user_id is required.');
        }

        $quantity = max(1, (int) ($record['quantity'] ?? 1));
        $unitPrice = is_numeric($record['unit_price'] ?? null) ? (float) $record['unit_price'] : 0;
        $totalPrice = round($quantity * $unitPrice, 2);

        $order = BookingOrder::create([
            'order_number' => trim($record['order_number'] ?? '') ?: 'IMPORT-' . uniqid(),
            'user_id' => $userId,
            'subtotal' => $totalPrice,
            'discount_amount' => is_numeric($record['discount_amount'] ?? null) ? (float) $record['discount_amount'] : 0,
            'tax_amount' => is_numeric($record['tax_amount'] ?? null) ? (float) $record['tax_amount'] : 0,
            'total' => $totalPrice,
            'currency' => trim($record['currency'] ?? 'USD'),
            'status' => trim($record['status'] ?? 'pending'),
            'payment_status' => trim($record['payment_status'] ?? 'unpaid'),
            'notes' => trim($record['notes'] ?? '') ?: null,
        ]);

        $order->update([
            'total' => max(0, (float) $order->subtotal - (float) $order->discount_amount + (float) $order->tax_amount),
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

        $order->sendBookingNotifications('created');

        if ($order->status === 'confirmed') {
            $order->sendBookingNotifications('confirmed');
        }
    }
}
