<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingComment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BookingCommentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('panel_bookings_comments');

        $user = auth()->user();

        $query = BookingComment::query()
            ->where('user_id', $user->id)
            ->with([
                'booking'
            ]);

        $copyQuery = deepClone($query);

        $query = $this->handleFilters($request, $query);

        $getListData = $this->getListsData($request, $query);

        if ($request->ajax()) {
            return $getListData;
        }

        $bookingIds = deepClone($copyQuery)
            ->pluck('booking_id')
            ->toArray();

        $allBookingsLists = Booking::query()
            ->whereIn('id', $bookingIds)
            ->get();

        // STATS

        $allCommentsCount = BookingComment::where('user_id', $user->id)->count();

        $activeCommentsCount = BookingComment::where('user_id', $user->id)
            ->where('is_active', true)
            ->count();

        $data = [
            'pageTitle' => 'My Booking Comments',

            'allBookingsLists' => $allBookingsLists,

            'allCommentsCount' => $allCommentsCount,
            'activeCommentsCount' => $activeCommentsCount,
        ];

        $data = array_merge($data, $getListData);

        return view('design_1.panel.bookings.comment.index', $data);
    }

    private function handleFilters(Request $request, Builder $query): Builder
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $booking_id = $request->get('booking_id');
        $search = $request->get('search');
        $sort = $request->get('sort');

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($booking_id)) {
            $query->where('booking_id', $booking_id);
        }

        if (!empty($search)) {

            $query->where('comment', 'like', "%{$search}%");
        }

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

    private function getListsData(Request $request, Builder $query)
    {
        $page = $request->get('page') ?? 1;

        $count = $this->perPage;

        $total = $query->count();

        $query->limit($count);
        $query->offset(($page - 1) * $count);

        $comments = $query->get();

        if ($request->ajax()) {

            return $this->getAjaxResponse(
                $request,
                $comments,
                $total,
                $count
            );
        }

        return [
            'comments' => $comments,

            'pagination' => $this->makePagination(
                $request,
                $comments,
                $total,
                $count,
                true
            ),
        ];
    }

    private function getAjaxResponse(Request $request, $comments, $total, $count)
    {
        $html = "";

        foreach ($comments as $commentRow) {

            $html .= (string)view()->make(
                'design_1.panel.bookings.comment.table_items',
                ['comment' => $commentRow]
            );
        }

        return response()->json([
            'data' => $html,

            'pagination' => $this->makePagination(
                $request,
                $comments,
                $total,
                $count,
                true
            )
        ]);
    }
}
