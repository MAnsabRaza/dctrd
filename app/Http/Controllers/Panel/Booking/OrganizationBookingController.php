<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrganizationBookingController extends Controller
{
    public function index(Request $request)
    {
        // Permission key apke project ke pattern se match karen
        // (config/permissions ya seeder mein add karna na bhoolen)
        $this->authorize("panel_bookings_organization");

        $user = auth()->user();

        // Sirf "organization" role wale user hi yahan tak pohnch sakein
        abort_if($user->role_name !== 'organization', 403);

        $query = Booking::query()
            ->where('creator_id', $user->id)
            ->withCount(['orders' => function ($q) {
                // agar "operators" / "customers" ka concept orders se related ho
                // to yahan adjust kar lena, abhi sirf orders count bana hai
            }]);

        $copyQuery = deepClone($query);
        $query = $this->handleFilters($request, $query);
        $getListData = $this->getListsData($request, $query, $user);

        if ($request->ajax()) {
            return $getListData;
        }

        // Filter dropdown ke liye sirf isi organization ki bookings
        $bookingsIds = deepClone($copyQuery)->pluck('id')->toArray();
        $allBookingsLists = Booking::query()->select('id', 'title')
            ->whereIn('id', $bookingsIds)->get();

        // Header stats
        $stats = [
            'bookings_count' => deepClone($copyQuery)->count(),
            'operators_count' => $this->getOperatorsCount($user),
            'customers_count' => $this->getCustomersCount($copyQuery),
        ];

        $data = [
            'pageTitle' => trans('panel.organization_bookings'),
            'allBookingsLists' => $allBookingsLists,
            'organization' => $user,
            'stats' => $stats,
        ];
        $data = array_merge($data, $getListData);

        return view('design_1.panel.bookings.organization.index', $data);
    }

    private function handleFilters(Request $request, Builder $query): Builder
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $booking_id = $request->get('booking_id', null);
        $status = $request->get('status', null);
        $sort = $request->get('sort');

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($booking_id)) {
            $query->where('id', $booking_id);
        }

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

    private function getListsData(Request $request, Builder $query, $user)
    {
        $page = $request->get('page') ?? 1;
        $count = $this->perPage;

        $total = $query->count();

        $query->limit($count);
        $query->offset(($page - 1) * $count);

        $bookings = $query->get();

        if ($request->ajax()) {
            return $this->getAjaxResponse($request, $bookings, $total, $count);
        }

        return [
            'bookings' => $bookings,
            'pagination' => $this->makePagination($request, $bookings, $total, $count, true),
        ];
    }

    private function getAjaxResponse(Request $request, $bookings, $total, $count)
    {
        $html = "";

        foreach ($bookings as $bookingRow) {
            $html .= (string)view()->make('design_1.panel.bookings.organization.card_item', ['booking' => $bookingRow]);
        }

        return response()->json([
            'data' => $html,
            'pagination' => $this->makePagination($request, $bookings, $total, $count, true)
        ]);
    }

    /**
     * Operators = is organization (organ_id) se attached instructors/staff
     * Apke users table mein organ_id column already hai, isi se count nikal rahe hain
     */
    private function getOperatorsCount($user): int
    {
        return \App\User::query()
            ->where('organ_id', $user->id)
            ->where('role_name', 'instructor')
            ->count();
    }

    /**
     * Customers = is organization ki bookings par order karne wale unique users
     */
    private function getCustomersCount($bookingsQuery): int
    {
        $bookingIds = deepClone($bookingsQuery)->pluck('id')->toArray();

        if (empty($bookingIds)) {
            return 0;
        }

        return \App\Models\BookingOrder::query()
            ->whereIn('booking_id', $bookingIds)
            ->distinct('seller_id')
            ->count('seller_id');
    }
}