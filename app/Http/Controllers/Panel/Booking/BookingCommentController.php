<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Comment;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BookingCommentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize("panel_bookings_comments");

        $user = auth()->user();

        $query = Comment::where('status', 'active')
            ->whereNotNull('booking_id')
            ->whereHas('booking', function ($query) use ($user) {
                $query->where('creator_id', $user->id);
            })
            ->with([
                'booking' => function ($query) {
                    $query->select('id', 'slug');
                },
                'user' => function ($qu) {
                    $qu->select('id', 'username', 'full_name', 'role_id', 'role_name', 'avatar', 'avatar_settings');
                },
                'replies'
            ]);

        $copyQuery = deepClone($query);
        $query = $this->handleFilters($request, $query);
        $getListData = $this->getListsData($request, $query, $user);

        if ($request->ajax()) {
            return $getListData;
        }

        $allCommentsCount = deepClone($copyQuery)->count();
        $repliedCommentsCount = deepClone($copyQuery)->whereNotNull('reply_id')->count();

        $bookingsIds = deepClone($copyQuery)->pluck('booking_id')->toArray();
        $allBookingsLists = Booking::query()->select('id')
            ->whereIn('id', $bookingsIds)->get();

        $usersIds = deepClone($copyQuery)->pluck('user_id')->toArray();
        $allUsersLists = User::query()->select('id', 'full_name')
            ->whereIn('id', $usersIds)->get();

        $data = [
            'pageTitle' => trans('panel.booking_comments'),
            'allCommentsCount' => $allCommentsCount,
            'repliedCommentsCount' => $repliedCommentsCount,
            'allBookingsLists' => $allBookingsLists,
            'allUsersLists' => $allUsersLists,
        ];
        $data = array_merge($data, $getListData);

        return view('design_1.panel.bookings.comments.index', $data);
    }

    private function handleFilters(Request $request, Builder $query): Builder
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $user_id = $request->get('user_id');
        $booking_id = $request->get('booking_id');
        $search = $request->get('search');
        $sort = $request->get('sort');

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($user_id)) {
            $query->where('user_id', $user_id);
        }

        if (!empty($booking_id)) {
            $query->where('booking_id', $booking_id);
        }

        if (!empty($search)) {
            $query->where('comment', "like", "%$search%");
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

        foreach ($comments->whereNull('viewed_at') as $comment) {
            $comment->update([
                'viewed_at' => time()
            ]);
        }

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
            $html .= (string)view()->make('design_1.panel.bookings.comments.table_items', ['comment' => $commentRow]);
        }

        return response()->json([
            'data' => $html,
            'pagination' => $this->makePagination($request, $comments, $total, $count, true)
        ]);
    }
}