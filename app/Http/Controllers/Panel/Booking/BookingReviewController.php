<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingReview;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BookingReviewController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('panel_bookings_reviews');

        $user = auth()->user();

        $query = BookingReview::query()
            ->where('customer_id', $user->id)
            ->with([
                'booking'
            ]);

        $copyQuery = deepClone($query);

        // HANDLE FILTERS
        $query = $this->handleFilters($request, $query);

        // LIST DATA
        $getListData = $this->getListsData($request, $query);

        // AJAX
        if ($request->ajax()) {
            return $getListData;
        }

        // BOOKINGS LIST

        $bookingIds = deepClone($copyQuery)
            ->pluck('booking_id')
            ->toArray();

        $allBookingsLists = Booking::query()
            ->whereIn('id', $bookingIds)
            ->get();

        // STATS

        $allReviewsCount = BookingReview::where(
            'customer_id',
            $user->id
        )->count();

        $activeReviewsCount = BookingReview::where(
            'customer_id',
            $user->id
        )
        ->where('status', 'active')
        ->count();

        $pendingReviewsCount = BookingReview::where(
            'customer_id',
            $user->id
        )
        ->where('status', 'pending')
        ->count();

        $data = [

            'pageTitle' => 'My Booking Reviews',

            'allBookingsLists' => $allBookingsLists,

            'allReviewsCount' => $allReviewsCount,
            'activeReviewsCount' => $activeReviewsCount,
            'pendingReviewsCount' => $pendingReviewsCount,
        ];

        $data = array_merge($data, $getListData);

        return view(
            'design_1.panel.bookings.review.index',
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HANDLE FILTERS
    |--------------------------------------------------------------------------
    */

    private function handleFilters(
        Request $request,
        Builder $query
    ): Builder {

        $from = $request->get('from');

        $to = $request->get('to');

        $booking_id = $request->get('booking_id');

        $status = $request->get('status');

        $search = $request->get('search');

        $sort = $request->get('sort');

        // DATE FILTER

        $query = fromAndToDateFilter(
            $from,
            $to,
            $query,
            'created_at'
        );

        // BOOKING FILTER

        if (!empty($booking_id)) {

            $query->where('booking_id', $booking_id);
        }

        // STATUS FILTER

        if (!empty($status)) {

            $query->where('status', $status);
        }

        // SEARCH

        if (!empty($search)) {

            $query->where('comment', 'like', "%{$search}%");
        }

        // SORT

        if (!empty($sort)) {

            switch ($sort) {

                case 'create_date_asc':

                    $query->orderBy('created_at', 'asc');

                    break;

                case 'create_date_desc':

                    $query->orderBy('created_at', 'desc');

                    break;
            }

        } else {

            $query->latest();
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | LIST DATA
    |--------------------------------------------------------------------------
    */

    private function getListsData(
        Request $request,
        Builder $query
    ) {

        $page = $request->get('page') ?? 1;

        $count = $this->perPage;

        $total = $query->count();

        $query->limit($count);

        $query->offset(($page - 1) * $count);

        $reviews = $query->get();

        // AJAX

        if ($request->ajax()) {

            return $this->getAjaxResponse(
                $request,
                $reviews,
                $total,
                $count
            );
        }

        return [

            'reviews' => $reviews,

            'pagination' => $this->makePagination(
                $request,
                $reviews,
                $total,
                $count,
                true
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX RESPONSE
    |--------------------------------------------------------------------------
    */

    private function getAjaxResponse(
        Request $request,
        $reviews,
        $total,
        $count
    ) {

        $html = "";

        foreach ($reviews as $reviewRow) {

            $html .= (string)view()->make(
                'design_1.panel.bookings.review.table_items',
                ['review' => $reviewRow]
            );
        }

        return response()->json([

            'data' => $html,

            'pagination' => $this->makePagination(
                $request,
                $reviews,
                $total,
                $count,
                true
            )
        ]);
    }
}