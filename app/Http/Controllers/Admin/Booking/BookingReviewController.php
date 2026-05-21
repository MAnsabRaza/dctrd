<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function store(Request $request)
    {
        $this->authorize('admin_booking_review_create');

        $this->validate($request, [
            'booking_id'      => 'required|exists:bookings,id',
            'rating'          => 'required|integer|min:1|max:5',
            'comment'         => 'required|string|max:2000',
            'value_rating'    => 'nullable|integer|min:1|max:5',
            'delivery_rating' => 'nullable|integer|min:1|max:5',
            'seller_rating'   => 'nullable|integer|min:1|max:5',
            'status'          => 'required|in:pending,active,rejected',
            'reply'           => 'nullable|string|max:2000',
        ]);

        $review = BookingReview::create([
            'booking_id'      => $request->booking_id,
            'order_id'        => $request->order_id ?? 0,
            'customer_id'     => Auth::id(),   // ← current logged-in user
            'rating'          => $request->rating,
            'comment'         => $request->comment,
            'value_rating'    => $request->value_rating,
            'delivery_rating' => $request->delivery_rating,
            'seller_rating'   => $request->seller_rating,
            'status'          => $request->status,
            'reply'           => $request->reply,
            'replied_at'      => $request->reply ? now() : null,
        ]);

        if ($review->status === 'active') {
            $this->sendReviewNotification($review);
        }

        return redirect(getAdminPanelUrl('/booking/review'))
            ->with('success', trans('admin/main.booking_review_created_successfully'));
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
        $wasActive = $review->status === 'active';

        $this->validate($request, [
            'status' => 'required|in:pending,active,rejected',
            'reply'  => 'nullable|string|max:2000',
        ]);

        $review->update([
            'status'     => $request->status,
            'reply'      => $request->reply,
            'replied_at' => $request->reply ? now() : $review->replied_at,
        ]);

        if (!$wasActive && $review->status === 'active') {
            $this->sendReviewNotification($review);
        }

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

    private function sendReviewNotification(BookingReview $review): void
    {
        $review->loadMissing(['booking', 'customer']);

        if (empty($review->booking) || empty($review->booking->creator_id)) {
            return;
        }

        $notifyOptions = [
            '[c.title]' => $review->booking->title,
            '[item_title]' => $review->booking->title,
            '[u.name]' => optional($review->customer)->full_name,
            '[rate.count]' => $review->rating,
        ];

        sendNotification('booking_new_rating', $notifyOptions, $review->booking->creator_id);
        sendNotification('booking_new_rating', $notifyOptions, 1);
    }
}
