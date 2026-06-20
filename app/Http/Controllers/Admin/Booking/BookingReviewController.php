<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingReview;
use Illuminate\Http\Request;

class BookingReviewController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_booking_review');

        $query = BookingReview::query();

        $totalReviews        = deepClone($query)->count();
        $publishedReviews    = deepClone($query)->where('status', 'active')->count();
        $ratesAverage        = deepClone($query)->avg('rates');
        $bookingsWithoutReview = Booking::where('status', 'active')
            ->whereDoesntHave('reviews')
            ->count();

        $query = $this->filters($query, $request);

        $reviews = $query->orderBy('created_at', 'desc')
            ->with([
                'bookings' => function ($query) {
                    $query->select('id', 'slug', 'title');
                },
                'creator' => function ($query) {
                    $query->select('id', 'full_name');
                },
            ])
            ->withCount('comments')
            ->paginate(10);

        $data = [
            'pageTitle'            => trans('update.admin_booking_reviews_list_title'),
            'totalReviews'         => $totalReviews,
            'publishedReviews'     => $publishedReviews,
            'ratesAverage'         => round($ratesAverage, 2),
            'bookingsWithoutReview'=> $bookingsWithoutReview,
            'reviews'              => $reviews,
        ];

        $booking_ids = $request->get('booking_ids');
        if (!empty($booking_ids)) {
            $data['bookings'] = Booking::select('id', 'title')->whereIn('id', $booking_ids)->get();
        }

        return view('admin.booking.reviews.list', $data);
    }

    private function filters($query, $request)
    {
        $from        = $request->get('from', null);
        $to          = $request->get('to', null);
        $search      = $request->get('search', null);
        $booking_ids = $request->get('booking_ids');
        $status      = $request->get('status', null);

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($search)) {
            $query->where('description', 'like', "%$search%");
        }

        if (!empty($booking_ids)) {
            $query->whereIn('booking_id', $booking_ids);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function toggleStatus($id)
    {
        $this->authorize('admin_booking_review_status_toggle');

        $review = BookingReview::findOrFail($id);

        $review->update([
            'status' => ($review->status == 'active') ? 'pending' : 'active',
        ]);

        $toastData = [
            'title'  => trans('public.request_success'),
            'msg'    => trans('update.review_status_changed'),
            'status' => 'success',
        ];

        return back()->with(['toast' => $toastData]);
    }

    public function reply(Request $request, $id)
    {
        $this->authorize('admin_booking_reviews_reply');

        $review = BookingReview::with(['bookings', 'creator', 'comments.user'])->findOrFail($id);

        $data = [
            'pageTitle' => trans('admin/pages/comments.reply_comment'),
            'review'    => $review,
        ];

        return view('admin.booking.reviews.comment_reply', $data);
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_review_delete');

        $review = BookingReview::findOrFail($id);
        $review->delete();

        $toastData = [
            'title'  => trans('public.request_success'),
            'msg'    => trans('update.review_deleted'),
            'status' => 'success',
        ];

        return back()->with(['toast' => $toastData]);
    }
}