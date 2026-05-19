<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingFavorite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BookingFavoriteController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('panel_bookings_favorites');

        $user = auth()->user();

        $query = BookingFavorite::query()
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

        $allFavoritesCount = BookingFavorite::where('user_id', $user->id)->count();

        $data = [
            'pageTitle' => 'My Booking Favorites',

            'allBookingsLists' => $allBookingsLists,

            'allFavoritesCount' => $allFavoritesCount,
        ];

        $data = array_merge($data, $getListData);

        return view('design_1.panel.bookings.favorite.index', $data);
    }

    private function handleFilters(Request $request, Builder $query): Builder
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $booking_id = $request->get('booking_id');
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

        $favorites = $query->get();

        if ($request->ajax()) {

            return $this->getAjaxResponse(
                $request,
                $favorites,
                $total,
                $count
            );
        }

        return [
            'favorites' => $favorites,

            'pagination' => $this->makePagination(
                $request,
                $favorites,
                $total,
                $count,
                true
            ),
        ];
    }

    private function getAjaxResponse(Request $request, $favorites, $total, $count)
    {
        $html = "";

        foreach ($favorites as $favoriteRow) {

            $html .= (string)view()->make(
                'design_1.panel.bookings.favorite.table_items',
                ['favorite' => $favoriteRow]
            );
        }

        return response()->json([
            'data' => $html,

            'pagination' => $this->makePagination(
                $request,
                $favorites,
                $total,
                $count,
                true
            )
        ]);
    }
}
