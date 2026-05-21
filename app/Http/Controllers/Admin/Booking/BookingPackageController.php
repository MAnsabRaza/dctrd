<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCategory;
use App\Models\BookingPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingPackageController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking');

        removeContentLocale();

        $packages = BookingPackage::with(['category', 'items.booking'])
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('admin.booking.package', $this->viewData($packages));
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_create');

        $package = BookingPackage::create($this->validatedPackage($request));
        $this->syncItems($package, $request);

        return redirect(getAdminPanelUrl('/booking/package'))
            ->with('success', trans('admin/main.created_successfully'));
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_edit');

        $editPackage = BookingPackage::with('items')->findOrFail($id);
        $packages = BookingPackage::with(['category', 'items.booking'])
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('admin.booking.package', $this->viewData($packages, $editPackage));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_edit');

        $package = BookingPackage::findOrFail($id);
        $package->update($this->validatedPackage($request, $package->id));
        $this->syncItems($package, $request);

        return redirect(getAdminPanelUrl('/booking/package'))
            ->with('success', trans('admin/main.updated_successfully'));
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_delete');

        BookingPackage::findOrFail($id)->delete();

        return redirect(getAdminPanelUrl('/booking/package'))
            ->with('success', trans('admin/main.deleted_successfully'));
    }

    private function viewData($packages, ?BookingPackage $editPackage = null): array
    {
        return [
            'pageTitle' => 'Booking Packages',
            'packages' => $packages,
            'editPackage' => $editPackage,
            'categories' => BookingCategory::orderBy('order')->get(['id', 'title']),
            'bookings' => Booking::orderBy('title')->get(['id', 'title']),
        ];
    }

    private function validatedPackage(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:booking_packages,slug,' . $id,
            'category_id' => 'nullable|exists:booking_categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'validity_days' => 'nullable|integer|min:1',
            'usage_limit' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,published,inactive',
            'featured' => 'nullable',
        ]);

        $data['creator_id'] = auth()->id();
        $data['slug'] = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['title']) . '-' . uniqid();
        $data['currency'] = $data['currency'] ?? 'USD';
        $data['featured'] = $request->get('featured') === 'on';

        return $data;
    }

    private function syncItems(BookingPackage $package, Request $request): void
    {
        $package->items()->delete();

        foreach ((array) $request->get('booking_ids', []) as $index => $bookingId) {
            if (empty($bookingId)) {
                continue;
            }

            $package->items()->create([
                'booking_id' => (int) $bookingId,
                'quantity' => max(1, (int) ($request->get('quantities')[$index] ?? 1)),
                'included_minutes' => !empty($request->get('included_minutes')[$index])
                    ? (int) $request->get('included_minutes')[$index]
                    : null,
                'sort_order' => $index,
            ]);
        }
    }
}
