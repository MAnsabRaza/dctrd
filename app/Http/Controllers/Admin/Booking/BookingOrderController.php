<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingBundle;
use App\Models\BookingOrder;
use App\Models\BookingOrderItem;
use App\Models\BookingResource;
use Illuminate\Http\Request;

class BookingOrderController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_orders');

        removeContentLocale();

        $orders = BookingOrder::with(['user', 'items.booking', 'items.bundle'])
            ->orderBy('id', 'desc')
            ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);
        $bundles = BookingBundle::orderBy('id', 'desc')->get(['id', 'title']);
        $resources = BookingResource::orderBy('name')->get(['id', 'name']);

        return view('admin.booking.order', [
            'pageTitle' => 'Booking Orders',
            'orders' => $orders,
            'bookings' => $bookings,
            'bundles' => $bundles,
            'resources' => $resources,
            'editOrder' => null,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_orders_create');

        $validated = $request->validate([
            'currency' => 'required|string|max:10',
            'status' => 'required|in:pending,confirmed,cancelled,completed,no_show',
            'payment_status' => 'required|in:unpaid,partial,paid,refunded',
            'notes' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'item_type' => 'required|array|min:1',
            'item_type.*' => 'required|in:booking,bundle',
            'item_id' => 'required|array|min:1',
            'item_id.*' => 'required|integer',
            'resource_id' => 'nullable|array',
            'resource_id.*' => 'nullable|exists:booking_resources,id',
            'booking_date' => 'nullable|array',
            'booking_date.*' => 'nullable|date',
            'start_time' => 'nullable|array',
            'start_time.*' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|array',
            'end_time.*' => 'nullable|date_format:H:i',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|integer|min:1',
            'persons' => 'required|array|min:1',
            'persons.*' => 'required|integer|min:1',
            'unit_price' => 'required|array|min:1',
            'unit_price.*' => 'required|numeric|min:0',
            'selected_variants' => 'nullable|array',
            'selected_variants.*' => 'nullable|string|max:255',
            'item_status' => 'nullable|array',
            'item_status.*' => 'nullable|in:pending,confirmed,cancelled,completed,no_show',
        ]);

        $subtotal = 0;
        $order = BookingOrder::create([
            'order_number' => 'TEMP-' . uniqid(),
            'user_id' => auth()->id(),
            'subtotal' => 0,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'tax_amount' => $validated['tax_amount'] ?? 0,
            'total' => 0,
            'currency' => $validated['currency'],
            'status' => $validated['status'],
            'payment_status' => $validated['payment_status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $order->order_number = 'ORDER-' . $order->id;
        $order->save();

        foreach ($validated['item_type'] as $index => $type) {
            $quantity = (int) ($validated['quantity'][$index] ?? 1);
            $unitPrice = (float) ($validated['unit_price'][$index] ?? 0);
            $totalPrice = round($quantity * $unitPrice, 2);

            $itemData = [
                'order_id' => $order->id,
                'item_type' => $type,
                'booking_id' => $type === 'booking' ? $validated['item_id'][$index] : null,
                'bundle_id' => $type === 'bundle' ? $validated['item_id'][$index] : null,
                'resource_id' => $validated['resource_id'][$index] ?? null,
                'booking_date' => $validated['booking_date'][$index] ?? null,
                'start_time' => $validated['start_time'][$index] ?? null,
                'end_time' => $validated['end_time'][$index] ?? null,
                'quantity' => $quantity,
                'persons' => (int) ($validated['persons'][$index] ?? 1),
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'selected_variants' => array_values(array_filter(array_map('trim', explode(',', $validated['selected_variants'][$index] ?? '')), fn($value) => $value !== '')),
                'status' => $validated['item_status'][$index] ?? 'pending',
            ];

            BookingOrderItem::create($itemData);
            $subtotal += $totalPrice;
        }

        $order->update([
            'subtotal' => $subtotal,
            'total' => max(0, $subtotal - ($validated['discount_amount'] ?? 0) + ($validated['tax_amount'] ?? 0)),
        ]);

        return redirect(getAdminPanelUrl('/booking/order'))
            ->with('success', 'Booking order created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_orders_edit');

        $editOrder = BookingOrder::with(['items'])->findOrFail($id);

        $orders = BookingOrder::with(['user', 'items.booking', 'items.bundle'])
            ->orderBy('id', 'desc')
            ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);
        $bundles = BookingBundle::orderBy('id', 'desc')->get(['id', 'title']);
        $resources = BookingResource::orderBy('name')->get(['id', 'name']);

        return view('admin.booking.order', [
            'pageTitle' => 'Booking Orders',
            'orders' => $orders,
            'bookings' => $bookings,
            'bundles' => $bundles,
            'resources' => $resources,
            'editOrder' => $editOrder,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_orders_edit');

        $order = BookingOrder::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'currency' => 'required|string|max:10',
            'status' => 'required|in:pending,confirmed,cancelled,completed,no_show',
            'payment_status' => 'required|in:unpaid,partial,paid,refunded',
            'notes' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'item_type' => 'required|array|min:1',
            'item_type.*' => 'required|in:booking,bundle',
            'item_id' => 'required|array|min:1',
            'item_id.*' => 'required|integer',
            'resource_id' => 'nullable|array',
            'resource_id.*' => 'nullable|exists:booking_resources,id',
            'booking_date' => 'nullable|array',
            'booking_date.*' => 'nullable|date',
            'start_time' => 'nullable|array',
            'start_time.*' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|array',
            'end_time.*' => 'nullable|date_format:H:i',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|integer|min:1',
            'persons' => 'required|array|min:1',
            'persons.*' => 'required|integer|min:1',
            'unit_price' => 'required|array|min:1',
            'unit_price.*' => 'required|numeric|min:0',
            'selected_variants' => 'nullable|array',
            'selected_variants.*' => 'nullable|string|max:255',
            'item_status' => 'nullable|array',
            'item_status.*' => 'nullable|in:pending,confirmed,cancelled,completed,no_show',
        ]);

        $subtotal = 0;

        $order->update([
            'currency' => $validated['currency'],
            'status' => $validated['status'],
            'payment_status' => $validated['payment_status'],
            'notes' => $validated['notes'] ?? null,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'tax_amount' => $validated['tax_amount'] ?? 0,
        ]);

        $order->items()->delete();

        foreach ($validated['item_type'] as $index => $type) {
            $quantity = (int) ($validated['quantity'][$index] ?? 1);
            $unitPrice = (float) ($validated['unit_price'][$index] ?? 0);
            $totalPrice = round($quantity * $unitPrice, 2);

            BookingOrderItem::create([
                'order_id' => $order->id,
                'item_type' => $type,
                'booking_id' => $type === 'booking' ? $validated['item_id'][$index] : null,
                'bundle_id' => $type === 'bundle' ? $validated['item_id'][$index] : null,
                'resource_id' => $validated['resource_id'][$index] ?? null,
                'booking_date' => $validated['booking_date'][$index] ?? null,
                'start_time' => $validated['start_time'][$index] ?? null,
                'end_time' => $validated['end_time'][$index] ?? null,
                'quantity' => $quantity,
                'persons' => (int) ($validated['persons'][$index] ?? 1),
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'selected_variants' => array_values(array_filter(array_map('trim', explode(',', $validated['selected_variants'][$index] ?? '')), fn($value) => $value !== '')),
                'status' => $validated['item_status'][$index] ?? 'pending',
            ]);

            $subtotal += $totalPrice;
        }

        $order->update([
            'subtotal' => $subtotal,
            'total' => max(0, $subtotal - ($validated['discount_amount'] ?? 0) + ($validated['tax_amount'] ?? 0)),
        ]);

        return redirect(getAdminPanelUrl('/booking/order'))
            ->with('success', 'Booking order updated successfully.');
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_orders_delete');

        $order = BookingOrder::findOrFail($id);
        $order->items()->delete();
        $order->delete();

        return redirect(getAdminPanelUrl('/booking/order'))
            ->with('success', 'Booking order deleted successfully.');
    }
}
