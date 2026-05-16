<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingReview;
use Illuminate\Http\Request;

class BookingReviewController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_review');

        removeContentLocale();

        $reviews  = BookingReview::with(['booking', 'customer'])
                        ->orderBy('id', 'desc')
                        ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        $data = [
            'pageTitle' => trans('admin/main.admin_booking_review'),
            'reviews'   => $reviews,
            'bookings'  => $bookings,
        ];

        return view('admin.booking.review', $data);
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_review_edit');

        $editReview = BookingReview::with(['booking', 'customer'])->findOrFail($id);
        $reviews    = BookingReview::with(['booking', 'customer'])
                        ->orderBy('id', 'desc')
                        ->paginate(20);
        $bookings   = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        $data = [
            'pageTitle'  => trans('admin/main.admin_booking_review'),
            'reviews'    => $reviews,
            'editReview' => $editReview,
            'bookings'   => $bookings,
        ];

        return view('admin.booking.review', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_review_edit');

        $review = BookingReview::findOrFail($id);

        $this->validate($request, [
            'status' => 'required|in:pending,active,rejected',
            'reply'  => 'nullable|string|max:2000',
        ]);

        $review->update([
            'status'     => $request->status,
            'reply'      => $request->reply,
            'replied_at' => $request->reply ? now() : $review->replied_at,
        ]);

        return redirect(getAdminPanelUrl('/booking/review'))
            ->with('success', trans('admin/main.booking_review_updated_successfully'));
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_review_delete');

        $review = BookingReview::findOrFail($id);
        $review->delete();

        return redirect(getAdminPanelUrl('/booking/review'))
            ->with('success', trans('admin/main.booking_review_deleted_successfully'));
    }
}