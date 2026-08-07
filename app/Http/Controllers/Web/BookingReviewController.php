<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingReviewController extends Controller
{
    public function getReviewsByBookingSlug(Request $request, $slug)
    {
        $booking = Booking::query()
            ->select('id', 'slug')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (empty($booking)) {
            abort(404);
        }

        $page = (int) $request->get('page', 1);
        $count = 10;

        $query = BookingReview::query()
            ->where('booking_id', $booking->id)
            ->where('status', 'active')
            ->with([
                'comments' => fn ($query) => $query->where('status', 'active'),
                'creator' => fn ($query) => $query->select('id', 'username', 'full_name', 'role_id', 'role_name', 'avatar', 'avatar_settings'),
            ])
            ->orderBy('created_at', 'desc');

        $total = $query->count();
        $reviews = $query->limit($count)->offset(($page - 1) * $count)->get();
        $hasMore = $total > ($page * $count);

        if ($request->ajax()) {
            $html = (string) view()->make('design_1.web.components.reviews.all_cards', ['reviews' => $reviews]);

            return response()->json([
                'code' => 200,
                'html' => $html,
                'has_more' => $hasMore,
            ]);
        }

        return [
            'reviews' => $reviews,
            'has_more' => $hasMore,
        ];
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (empty($user)) {
            return response()->json(['code' => 401], 401);
        }

        $data = $request->all();

        $validator = Validator::make($data, [
            'booking_id' => 'required|exists:bookings,id',
            'booking_quality' => 'required|integer|min:1|max:5',
            'provider_quality' => 'required|integer|min:1|max:5',
            'value_for_money' => 'required|integer|min:1|max:5',
            'location_quality' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $booking = Booking::query()
            ->where('id', $data['booking_id'])
            ->where('status', 'published')
            ->first();

        if (empty($booking)) {
            abort(404);
        }

        $existingReview = BookingReview::query()
            ->where('creator_id', $user->id)
            ->where('booking_id', $booking->id)
            ->first();

        if (!empty($existingReview)) {
            return response()->json([
                'toast_alert' => [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('public.duplicate_review_for_product'),
                ],
            ], 422);
        }

        $rates = (
            (int) $data['booking_quality'] +
            (int) $data['provider_quality'] +
            (int) $data['value_for_money'] +
            (int) $data['location_quality']
        ) / 4;

        BookingReview::create([
            'booking_id' => $booking->id,
            'creator_id' => $user->id,
            'product_quality' => (int) $data['booking_quality'],
            'purchase_worth' => (int) $data['value_for_money'],
            'delivery_quality' => (int) $data['location_quality'],
            'seller_quality' => (int) $data['provider_quality'],
            'rates' => $rates,
            'description' => $data['description'] ?? null,
            'status' => 'pending',
            'created_at' => time(),
        ]);

        return response()->json([
            'code' => 200,
            'title' => trans('public.request_success'),
            'msg' => trans('update.review_submitted_successfully'),
        ]);
    }

  public function storeReplyComment(Request $request)
{
    $user = auth()->user();
    $data = $request->all();

    $validator = Validator::make($data, [
        'reply' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'code'   => 422,
            'errors' => $validator->errors(),
        ], 422);
    }

    if (!empty($user)) {
        $status = Comment::$pending;
        if (!empty(getGeneralOptionsSettings('direct_publication_of_comments'))) {
            $status = Comment::$active;
        }

        Comment::create([
            'user_id'    => $user->id,
            'comment'    => $data['reply'],
            'review_id'  => $data['review_id'],
            'status'     => $status,
            'created_at' => time(),
        ]);

        return response()->json([
            'code'  => 200,
            'title' => trans('product.comment_success_store'),
            'msg'   => trans('product.comment_success_store_msg'),
        ]);
    }

    abort(403);
}

    public function destroy($id)
    {
        $user = auth()->user();

        if (empty($user)) {
            abort(403);
        }

        BookingReview::query()
            ->where('id', $id)
            ->where('creator_id', $user->id)
            ->delete();

        return back();
    }
}
