<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingOrder;
use App\Models\BookingReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingReviewController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_review');

        removeContentLocale();

        $reviews  = BookingReview::with(['booking', 'customer', 'order'])
                        ->orderBy('id', 'desc')
                        ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        $orders = BookingOrder::with('items.booking')
                        ->orderBy('id', 'desc')
                        ->get(['id', 'order_number', 'user_id', 'status']);

        return view('admin.booking.review', [
            'pageTitle'  => trans('admin/main.admin_booking_review'),
            'reviews'    => $reviews,
            'bookings'   => $bookings,
            'orders'     => $orders,
            'editReview' => null,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_review_create');

        $this->validate($request, [
            'booking_id'      => 'required|exists:bookings,id',
            'order_id'        => 'required|exists:booking_orders,id',
            'rating'          => 'required|integer|min:1|max:5',
            'comment'         => 'required|string|max:2000',
            'value_rating'    => 'nullable|integer|min:1|max:5',
            'delivery_rating' => 'nullable|integer|min:1|max:5',
            'seller_rating'   => 'nullable|integer|min:1|max:5',
            'status'          => 'required|in:pending,active,rejected',
            'reply'           => 'nullable|string|max:2000',
        ]);

        // Unique check: one review per customer per order
        $alreadyExists = BookingReview::where('order_id', $request->order_id)
                                      ->where('customer_id', Auth::id())
                                      ->exists();

        if ($alreadyExists) {
            return back()->withInput()->withErrors([
                'order_id' => 'A review for this order already exists.',
            ]);
        }

        $review = BookingReview::create([
            'booking_id'      => $request->booking_id,
            'order_id'        => $request->order_id,
            'customer_id'     => Auth::id(),
            'rating'          => $request->rating,
            'comment'         => $request->comment,
            'value_rating'    => $request->value_rating,
            'delivery_rating' => $request->delivery_rating,
            'seller_rating'   => $request->seller_rating,
            'status'          => $request->status,
            'reply'           => $request->reply,
            'replied_at'      => $request->filled('reply') ? now() : null,
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

        $editReview = BookingReview::with(['booking', 'customer', 'order'])->findOrFail($id);

        $reviews  = BookingReview::with(['booking', 'customer', 'order'])
                        ->orderBy('id', 'desc')
                        ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        $orders = BookingOrder::with('items.booking')
                        ->orderBy('id', 'desc')
                        ->get(['id', 'order_number', 'user_id', 'status']);

        return view('admin.booking.review', [
            'pageTitle'  => trans('admin/main.admin_booking_review'),
            'reviews'    => $reviews,
            'editReview' => $editReview,
            'bookings'   => $bookings,
            'orders'     => $orders,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_review_edit');

        $review    = BookingReview::findOrFail($id);
        $wasActive = $review->status === 'active';

        // ✅ All fields are now editable
        $this->validate($request, [
            'rating'          => 'required|integer|min:1|max:5',
            'comment'         => 'required|string|max:2000',
            'value_rating'    => 'nullable|integer|min:1|max:5',
            'delivery_rating' => 'nullable|integer|min:1|max:5',
            'seller_rating'   => 'nullable|integer|min:1|max:5',
            'status'          => 'required|in:pending,active,rejected',
            'reply'           => 'nullable|string|max:2000',
        ]);

        $replyChanged = $request->filled('reply') && $request->reply !== $review->reply;

        $review->update([
            'rating'          => $request->rating,
            'comment'         => $request->comment,
            'value_rating'    => $request->value_rating,
            'delivery_rating' => $request->delivery_rating,
            'seller_rating'   => $request->seller_rating,
            'status'          => $request->status,
            'reply'           => $request->reply,
            'replied_at'      => $replyChanged ? now() : $review->replied_at,
        ]);

        if (!$wasActive && $review->fresh()->status === 'active') {
            $this->sendReviewNotification($review);
        }

        return redirect(getAdminPanelUrl('/booking/review'))
            ->with('success', trans('admin/main.booking_review_updated_successfully'));
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_review_delete');

        BookingReview::findOrFail($id)->delete();

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
            '[c.title]'    => $review->booking->title,
            '[item_title]' => $review->booking->title,
            '[u.name]'     => optional($review->customer)->full_name,
            '[rate.count]' => $review->rating,
        ];

        sendNotification('booking_new_rating', $notifyOptions, $review->booking->creator_id);
        sendNotification('booking_new_rating', $notifyOptions, 1);
    }
}