<?php
namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingBundle;
use App\Models\BookingBundleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingBundleController extends Controller
{
    private function getUserLanguages(): array
    {
        $userLanguages = getUserLanguagesLists();

        if (empty($userLanguages)) {
            return [app()->getLocale() => ucfirst(app()->getLocale())];
        }

        return $userLanguages;
    }

    public function index()
    {
        $this->authorize('admin_booking_bundle');

        removeContentLocale();

        $bundles  = BookingBundle::with(['creator', 'items.booking'])
            ->orderBy('id', 'desc')
            ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        return view('admin.booking.bookingBundle', [
            'pageTitle'  => 'Booking Bundles',
            'bundles'    => $bundles,
            'bookings'   => $bookings,
            'editBundle' => null,
            'userLanguages' => $this->getUserLanguages(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_bundle_create');

        $request->merge([
            'featured' => $request->has('featured') ? 1 : 0,
            'slug'     => $request->filled('slug')
                ? Str::slug($request->slug)
                : Str::slug($request->title) . '-' . time(),
        ]);

        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'slug'                => 'required|string|unique:booking_bundles,slug',
            'description'         => 'nullable|string',
            'thumbnail'           => 'nullable|string',
            'cover'               => 'nullable|string',
            'language'            => 'nullable|string|max:10',
            'price'               => 'required|numeric|min:0',
            'discount_price'      => 'nullable|numeric|min:0',
            'currency'            => 'nullable|string|max:10',
            'validity_days'       => 'nullable|integer|min:1',
            'availability_status' => 'nullable|string',
            'availability_note'   => 'nullable|string',
            'status'              => 'required|in:draft,pending,published,rejected,inactive',
            'featured'            => 'boolean',
            // bundle items
            'booking_ids'         => 'required|array|min:1',
            'booking_ids.*'       => 'exists:bookings,id',
            'quantities'          => 'required|array|min:1',
            'quantities.*'        => 'integer|min:1',
        ]);

        $bookingIds = $validated['booking_ids'];
        $quantities = $validated['quantities'];
        unset($validated['booking_ids'], $validated['quantities']);

        $validated['creator_id'] = auth()->id();

        $bundle = BookingBundle::create($validated);

        // items save کریں
        foreach ($bookingIds as $index => $bookingId) {
            BookingBundleItem::create([
                'bundle_id'  => $bundle->id,
                'booking_id' => $bookingId,
                'quantity'   => $quantities[$index] ?? 1,
                'sort_order' => $index + 1,
            ]);
        }

        return redirect(getAdminPanelUrl('/booking/bundle'))
            ->with('success', 'Bundle created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_bundle_edit');

        $editBundle = BookingBundle::with(['items.booking'])->findOrFail($id);

        $bundles  = BookingBundle::with(['creator', 'items.booking'])
            ->orderBy('id', 'desc')
            ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        return view('admin.booking.bookingBundle', [
            'pageTitle'  => 'Edit Bundle',
            'bundles'    => $bundles,
            'bookings'   => $bookings,
            'editBundle' => $editBundle,
            'userLanguages' => $this->getUserLanguages(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_bundle_edit');

        $bundle = BookingBundle::findOrFail($id);

        $request->merge([
            'featured' => $request->has('featured') ? 1 : 0,
            'slug'     => $request->filled('slug')
                ? Str::slug($request->slug)
                : $bundle->slug,
        ]);

        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'slug'                => 'required|string|unique:booking_bundles,slug,' . $id,
            'description'         => 'nullable|string',
            'thumbnail'           => 'nullable|string',
            'cover'               => 'nullable|string',
            'language'            => 'nullable|string|max:10',
            'price'               => 'required|numeric|min:0',
            'discount_price'      => 'nullable|numeric|min:0',
            'currency'            => 'nullable|string|max:10',
            'validity_days'       => 'nullable|integer|min:1',
            'availability_status' => 'nullable|string',
            'availability_note'   => 'nullable|string',
            'status'              => 'required|in:draft,pending,published,rejected,inactive',
            'featured'            => 'boolean',
            'booking_ids'         => 'required|array|min:1',
            'booking_ids.*'       => 'exists:bookings,id',
            'quantities'          => 'required|array|min:1',
            'quantities.*'        => 'integer|min:1',
        ]);

        $bookingIds = $validated['booking_ids'];
        $quantities = $validated['quantities'];
        unset($validated['booking_ids'], $validated['quantities']);

        $bundle->update($validated);

        // پرانے items delete کر کے نئے save کریں
        $bundle->items()->delete();
        foreach ($bookingIds as $index => $bookingId) {
            BookingBundleItem::create([
                'bundle_id'  => $bundle->id,
                'booking_id' => $bookingId,
                'quantity'   => $quantities[$index] ?? 1,
                'sort_order' => $index + 1,
            ]);
        }

        return redirect(getAdminPanelUrl('/booking/bundle'))
            ->with('success', 'Bundle updated successfully.');
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_bundle_delete');

        $bundle = BookingBundle::findOrFail($id);
        $bundle->items()->delete();
        $bundle->delete();

        return redirect(getAdminPanelUrl('/booking/bundle'))
            ->with('success', 'Bundle deleted successfully.');
    }
}
