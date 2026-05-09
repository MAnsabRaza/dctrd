<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingPolicy;
use Illuminate\Http\Request;

class BookingPolicyController extends Controller
{
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
            'editPolicy' => null,   // ← MUST always be passed
        ];

        return view('admin.booking.policy', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_polices_create');

        $request->merge([
            'reschedule_allowed' => $request->has('reschedule_allowed') ? 1 : 0,
            'deposit_required'   => $request->has('deposit_required')   ? 1 : 0,
        ]);

        $this->validate($request, [
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

        BookingPolicy::create([
            'booking_id'               => $request->booking_id,
            'cancellation_type'        => $request->cancellation_type,
            'free_cancel_hours'        => $request->free_cancel_hours        ?? 24,
            'cancellation_fee_percent' => $request->cancellation_fee_percent ?? 0,
            'reschedule_allowed'       => $request->reschedule_allowed,
            'reschedule_before_hours'  => $request->reschedule_before_hours  ?? 24,
            'max_reschedules'          => $request->max_reschedules          ?? 2,
            'noshow_fee_percent'       => $request->noshow_fee_percent       ?? 100,
            'deposit_required'         => $request->deposit_required,
            'deposit_percent'          => $request->deposit_percent          ?? 20,
            'deposit_due_days'         => $request->deposit_due_days         ?? 0,
            'balance_due_days_before'  => $request->balance_due_days_before  ?? 0,
            'policy_text'              => $request->policy_text              ?? null,
        ]);

        return redirect(getAdminPanelUrl('/booking/policy'))
            ->with('success', trans('admin/main.booking_policy_created_successfully'));
    }

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

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_polices_edit');

        $policy = BookingPolicy::findOrFail($id);

        $request->merge([
            'reschedule_allowed' => $request->has('reschedule_allowed') ? 1 : 0,
            'deposit_required'   => $request->has('deposit_required')   ? 1 : 0,
        ]);

        $this->validate($request, [
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

        $policy->update([
            'booking_id'               => $request->booking_id,
            'cancellation_type'        => $request->cancellation_type,
            'free_cancel_hours'        => $request->free_cancel_hours        ?? 24,
            'cancellation_fee_percent' => $request->cancellation_fee_percent ?? 0,
            'reschedule_allowed'       => $request->reschedule_allowed,
            'reschedule_before_hours'  => $request->reschedule_before_hours  ?? 24,
            'max_reschedules'          => $request->max_reschedules          ?? 2,
            'noshow_fee_percent'       => $request->noshow_fee_percent       ?? 100,
            'deposit_required'         => $request->deposit_required,
            'deposit_percent'          => $request->deposit_percent          ?? 20,
            'deposit_due_days'         => $request->deposit_due_days         ?? 0,
            'balance_due_days_before'  => $request->balance_due_days_before  ?? 0,
            'policy_text'              => $request->policy_text              ?? null,
        ]);

        return redirect(getAdminPanelUrl('/booking/policy'))
            ->with('success', trans('admin/main.booking_policy_updated_successfully'));
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_polices_delete');

        $policy = BookingPolicy::findOrFail($id);
        $policy->delete();

        return redirect(getAdminPanelUrl('/booking/policy'))
            ->with('success', trans('admin/main.booking_policy_deleted_successfully'));
    }
}