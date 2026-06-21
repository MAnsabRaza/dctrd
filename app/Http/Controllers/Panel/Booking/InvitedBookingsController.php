<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingPartnerUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class InvitedBookingsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize("panel_bookings_invited_lists");

        $user = auth()->user();

        if ($user->isUser()) {
            abort(404);
        }

        // Sirf wo bookings jin ka invite is user (partner) ko mila hai
        $invitedBookingIds = BookingPartnerUser::query()
            ->where('user_id', $user->id)
            ->pluck('booking_id')
            ->toArray();

        $query = Booking::query()->whereIn('id', $invitedBookingIds);

        $copyQuery = deepClone($query);
        $query = $this->handleFilters($request, $query);
        $getListData = $this->getListsData($request, $query);

        if ($request->ajax()) {
            return $getListData;
        }

        $stats = [
            'bookings_count' => deepClone($copyQuery)->count(),
            'bookings_sales' => $this->getSalesTotal($invitedBookingIds),
        ];

        $data = [
            'pageTitle' => trans('panel.invited_bookings'),
            'stats'     => $stats,
        ];
        $data = array_merge($data, $getListData);

        return view('design_1.panel.bookings.invitations.index', $data);
    }

    private function handleFilters(Request $request, Builder $query): Builder
    {
        $from   = $request->get('from', null);
        $to     = $request->get('to', null);
        $status = $request->get('status', null);
        $sort   = $request->get('sort');

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'create_date_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'create_date_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'title_asc':
                    $query->orderBy('title', 'asc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    private function getListsData(Request $request, Builder $query)
    {
        $page  = $request->get('page') ?? 1;
        $count = $this->perPage;

        $total = $query->count();

        $query->limit($count);
        $query->offset(($page - 1) * $count);

        $bookings = $query->get();

        if ($request->ajax()) {
            return $this->getAjaxResponse($request, $bookings, $total, $count);
        }

        return [
            'bookings'   => $bookings,
            'pagination' => $this->makePagination($request, $bookings, $total, $count, true),
        ];
    }

    private function getAjaxResponse(Request $request, $bookings, $total, $count)
    {
        $html = "";

        foreach ($bookings as $bookingRow) {
            $html .= (string) view()->make('design_1.panel.bookings.invitations.card_item', [
                'booking' => $bookingRow,
            ]);
        }

        return response()->json([
            'data'       => $html,
            'pagination' => $this->makePagination($request, $bookings, $total, $count, true),
        ]);
    }

    /**
     * NOTE: 'status' aur 'total_price' column names apne BookingOrder
     * schema ke mutabiq adjust kar len (jaisa "completed/paid" status hota hai)
     */
    private function getSalesTotal(array $bookingIds): float
    {
        if (empty($bookingIds)) {
            return 0;
        }

        return (float) \App\Models\BookingOrder::query()
            ->whereIn('booking_id', $bookingIds)
            ->where('status', 'completed')
            ->sum('total_price');
    }
}