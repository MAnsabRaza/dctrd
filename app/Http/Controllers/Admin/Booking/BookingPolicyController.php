<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingPolicyController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // LIST
    // ─────────────────────────────────────────────────────────────────────
    public function index()
    {
        $this->authorize('admin_booking_polices');

        removeContentLocale();

        $policies = BookingPolicy::with(['booking'])
            ->orderBy('id', 'desc')
            ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        $data = [
            'pageTitle'  => trans('admin/main.admin_booking_polices'),
            'policies'   => $policies,
            'bookings'   => $bookings,
            'editPolicy' => null,
        ];

        return view('admin.booking.policy', $data);
    }

    // ─────────────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $this->authorize('admin_booking_polices_create');

        // ── Normalize checkbox fields ─────────────────────────────────────
        // Checkboxes send "on" when checked, nothing when unchecked.
        // We convert them to boolean integers before validation.
        $request->merge([
            'reschedule_allowed' => $request->has('reschedule_allowed') ? 1 : 0,
            'deposit_required'   => $request->has('deposit_required')   ? 1 : 0,
        ]);

        // ── Validation ───────────────────────────────────────────────────
        $validated = $request->validate([
            'booking_id'               => 'required|integer|exists:bookings,id|unique:booking_policies,booking_id',
            'cancellation_type'        => 'required|in:flexible,moderate,strict,non_refundable',
            'free_cancel_hours'        => 'nullable|integer|min:0',
            'cancellation_fee_percent' => 'nullable|numeric|min:0|max:100',
            'reschedule_allowed'       => 'required|boolean',
            'reschedule_before_hours'  => 'nullable|integer|min:0',
            'max_reschedules'          => 'nullable|integer|min:0',
            'noshow_fee_percent'       => 'nullable|numeric|min:0|max:100',
            'deposit_required'         => 'required|boolean',
            'deposit_percent'          => 'nullable|numeric|min:0|max:100',
            'deposit_due_days'         => 'nullable|integer|min:0',
            'balance_due_days_before'  => 'nullable|integer|min:0',
            'policy_text'              => 'nullable|string',
        ]);

        // ── Create ───────────────────────────────────────────────────────
        BookingPolicy::create([
            'booking_id'               => (int) $validated['booking_id'],
            'cancellation_type'        => $validated['cancellation_type'],
            'free_cancel_hours'        => (int) ($validated['free_cancel_hours']        ?? 24),
            'cancellation_fee_percent' => (float) ($validated['cancellation_fee_percent'] ?? 0),
            'reschedule_allowed'       => (bool) $validated['reschedule_allowed'],
            'reschedule_before_hours'  => (int) ($validated['reschedule_before_hours']  ?? 24),
            'max_reschedules'          => (int) ($validated['max_reschedules']           ?? 2),
            'noshow_fee_percent'       => (float) ($validated['noshow_fee_percent']      ?? 100),
            'deposit_required'         => (bool) $validated['deposit_required'],
            'deposit_percent'          => (float) ($validated['deposit_percent']         ?? 20),
            'deposit_due_days'         => (int) ($validated['deposit_due_days']          ?? 0),
            'balance_due_days_before'  => (int) ($validated['balance_due_days_before']  ?? 0),
            'policy_text'              => $validated['policy_text'] ?? null,
        ]);

        return redirect(getAdminPanelUrl('/booking/policy'))
            ->with('success', trans('admin/main.booking_policy_created_successfully'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────────────
    public function edit($id)
    {
        $this->authorize('admin_booking_polices_edit');

        $editPolicy = BookingPolicy::findOrFail($id);

        $policies = BookingPolicy::with(['booking'])
            ->orderBy('id', 'desc')
            ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        $data = [
            'pageTitle'  => trans('admin/main.admin_booking_polices'),
            'policies'   => $policies,
            'editPolicy' => $editPolicy,
            'bookings'   => $bookings,
        ];

        return view('admin.booking.policy', $data);
    }

    // ─────────────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_polices_edit');

        $policy = BookingPolicy::findOrFail($id);

        // ── Normalize checkbox fields ─────────────────────────────────────
        $request->merge([
            'reschedule_allowed' => $request->has('reschedule_allowed') ? 1 : 0,
            'deposit_required'   => $request->has('deposit_required')   ? 1 : 0,
        ]);

        // ── Validation ───────────────────────────────────────────────────
        // unique rule ignores current record's own booking_id
        $validated = $request->validate([
            'booking_id'               => 'required|integer|exists:bookings,id|unique:booking_policies,booking_id,' . $id,
            'cancellation_type'        => 'required|in:flexible,moderate,strict,non_refundable',
            'free_cancel_hours'        => 'nullable|integer|min:0',
            'cancellation_fee_percent' => 'nullable|numeric|min:0|max:100',
            'reschedule_allowed'       => 'required|boolean',
            'reschedule_before_hours'  => 'nullable|integer|min:0',
            'max_reschedules'          => 'nullable|integer|min:0',
            'noshow_fee_percent'       => 'nullable|numeric|min:0|max:100',
            'deposit_required'         => 'required|boolean',
            'deposit_percent'          => 'nullable|numeric|min:0|max:100',
            'deposit_due_days'         => 'nullable|integer|min:0',
            'balance_due_days_before'  => 'nullable|integer|min:0',
            'policy_text'              => 'nullable|string',
        ]);

        // ── Update ───────────────────────────────────────────────────────
        $policy->update([
            'booking_id'               => (int) $validated['booking_id'],
            'cancellation_type'        => $validated['cancellation_type'],
            'free_cancel_hours'        => (int) ($validated['free_cancel_hours']        ?? 24),
            'cancellation_fee_percent' => (float) ($validated['cancellation_fee_percent'] ?? 0),
            'reschedule_allowed'       => (bool) $validated['reschedule_allowed'],
            'reschedule_before_hours'  => (int) ($validated['reschedule_before_hours']  ?? 24),
            'max_reschedules'          => (int) ($validated['max_reschedules']           ?? 2),
            'noshow_fee_percent'       => (float) ($validated['noshow_fee_percent']      ?? 100),
            'deposit_required'         => (bool) $validated['deposit_required'],
            'deposit_percent'          => (float) ($validated['deposit_percent']         ?? 20),
            'deposit_due_days'         => (int) ($validated['deposit_due_days']          ?? 0),
            'balance_due_days_before'  => (int) ($validated['balance_due_days_before']  ?? 0),
            'policy_text'              => $validated['policy_text'] ?? null,
        ]);

        return redirect(getAdminPanelUrl('/booking/policy'))
            ->with('success', trans('admin/main.booking_policy_updated_successfully'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────────────────────────────────
    public function delete($id)
    {
        $this->authorize('admin_booking_polices_delete');

        $policy = BookingPolicy::findOrFail($id);
        $policy->delete();

        return redirect(getAdminPanelUrl('/booking/policy'))
            ->with('success', trans('admin/main.booking_policy_deleted_successfully'));
    }
}