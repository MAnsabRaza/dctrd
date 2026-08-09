<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MyBookingCommentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize("panel_bookings_my_comments");

        $user = auth()->user();

        $query = Comment::query()->where('user_id', $user->id)
            ->whereNotNull('booking_id')
            ->with([
                'booking' => function ($query) {
                    $query->select('id', 'slug','title');
                }
            ]);

        $copyQuery = deepClone($query);
        $query = $this->handleFilters($request, $query);
        $getListData = $this->getListsData($request, $query, $user);

        if ($request->ajax()) {
            return $getListData;
        }

        $bookingsIds = deepClone($copyQuery)->pluck('booking_id')->toArray();
        $allBookingsLists = Booking::query()->select('id')
            ->whereIn('id', $bookingsIds)->get();

        $data = [
            'pageTitle' => trans('panel.my_booking_comments'),
            'allBookingsLists' => $allBookingsLists,
        ];
        $data = array_merge($data, $getListData);

        return view('design_1.panel.bookings.my_comments.index', $data);
    }

    private function handleFilters(Request $request, Builder $query): Builder
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $booking_id = $request->get('booking_id', null);
        $sort = $request->get('sort');

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($booking_id)) {
            $query->where('booking_id', $booking_id);
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
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    private function getListsData(Request $request, Builder $query, $user)
    {
        $page = $request->get('page') ?? 1;
        $count = $this->perPage;

        $total = $query->count();

        $query->limit($count);
        $query->offset(($page - 1) * $count);

        $comments = $query->get();

        if ($request->ajax()) {
            return $this->getAjaxResponse($request, $comments, $total, $count);
        }

        return [
            'comments' => $comments,
            'pagination' => $this->makePagination($request, $comments, $total, $count, true),
        ];
    }

    private function getAjaxResponse(Request $request, $comments, $total, $count)
    {
        $html = "";

        foreach ($comments as $commentRow) {
            $html .= (string)view()->make('design_1.panel.bookings.my_comments.table_items', ['comment' => $commentRow]);
        }

        return response()->json([
            'data' => $html,
            'pagination' => $this->makePagination($request, $comments, $total, $count, true)
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();

        $comment = Comment::where('user_id', $user->id)->findOrFail($id);
        $comment->delete();

        return redirect()->back();
    }
    public function update(Request $request, $id)
{
    $user = auth()->user();

    $comment = Comment::where('user_id', $user->id)->findOrFail($id);

    $this->validate($request, [
        'comment' => 'required|string',
        'status'  => 'nullable|in:active,pending',
    ]);

    $comment->update([
        'comment' => $request->input('comment'),
        'status'  => $request->input('status', $comment->status),
    ]);

    if ($request->ajax()) {
        return response()->json([
            'code' => 200,
            'msg'  => trans('public.request_success'),
        ]);
    }

    return redirect()->back();
}
}