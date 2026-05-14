<?php
namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingImport;
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
            'file' => 'required|file|mimes:csv,txt|max:5120',
            'type' => 'required|in:bookings,orders',
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

        // Process immediately (or dispatch a job for large files)
        $this->processImport($import);

        return redirect(getAdminPanelUrl('/booking/import'))
            ->with('success', 'Import completed. Success: ' . $import->success_rows . ', Failed: ' . $import->failed_rows);
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

        $headers = ['title', 'description', 'price', 'capacity', 'status'];
        $rows = [
            ['Sample Booking 1', 'Description here', '100.00', '10', '1'],
            ['Sample Booking 2', 'Description here', '200.00', '5', '1'],
        ];

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
                    $title = trim($record['title'] ?? '');

                    if (empty($title)) {
                        throw new \Exception('Title is required.');
                    }

                    Booking::create([
                        'title' => $title,
                        'description' => trim($record['description'] ?? ''),
                        'price' => is_numeric($record['price'] ?? null) ? (float) $record['price'] : 0,
                        'capacity' => is_numeric($record['capacity'] ?? null) ? (int) $record['capacity'] : 1,
                        'status' => isset($record['status']) ? (bool) (int) $record['status'] : true,
                    ]);

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
}