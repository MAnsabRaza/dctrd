<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use JsonException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('panel_bookings');

        $user = auth()->user();

        $query = Booking::query()
            ->with('category')
            ->orderBy('created_at', 'desc');

        $copyQuery = deepClone($query);

        $query = $this->handleFilters($request, $query);

        $getListData = $this->getListsData($request, $query);

        if ($request->ajax()) {
            return $getListData;
        }

        $categoryIds = deepClone($copyQuery)->pluck('category_id')->filter()->toArray();
        $allCategoryLists = BookingCategory::query()
            ->select('id', 'title')
            ->whereIn('id', $categoryIds)
            ->orderBy('title')
            ->get();

        $data = [
            'pageTitle'        => trans('panel.booking_management'),
            'allCategoryLists' => $allCategoryLists,
        ];

        $data = array_merge($data, $getListData);

        return view('design_1.panel.bookings.index', $data);
    }

    public function create(Request $request)
    {
        $this->authorize('panel_bookings');

        $allCategoryLists = BookingCategory::query()
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        return view('design_1.panel.bookings.create.index', [
            'pageTitle' => trans('panel.new_booking'),
            'allCategoryLists' => $allCategoryLists,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('panel_bookings');

        $booking = Booking::findOrFail($id);
        $allCategoryLists = BookingCategory::query()
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        return view('design_1.panel.bookings.create.index', [
            'pageTitle' => trans('panel.edit_booking'),
            'booking' => $booking,
            'allCategoryLists' => $allCategoryLists,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('panel_bookings');

        $data    = $this->validateBooking($request);
        $booking = Booking::create($data);

        if ($request->ajax()) {
            return response()->json([
                'message' => trans('panel.booking_created'),
                'booking' => $this->transformBooking($booking->load('category')),
            ]);
        }

        return redirect()->route('panel.bookings.index')
            ->with('success', trans('panel.booking_created'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('panel_bookings');

        $booking = Booking::findOrFail($id);

        $data = $this->validateBooking($request, $booking->id);
        $booking->update($data);

        if ($request->ajax()) {
            return response()->json([
                'message' => trans('panel.booking_updated'),
                'booking' => $this->transformBooking($booking->load('category')),
            ]);
        }

        return redirect()->route('panel.bookings.index')
            ->with('success', trans('panel.booking_updated'));
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('panel_bookings');

        $booking = Booking::findOrFail($id);
        $booking->delete();

        if ($request->ajax()) {
            return response()->json([
                'message' => trans('panel.booking_deleted'),
            ]);
        }

        return redirect()->route('panel.bookings.index')
            ->with('success', trans('panel.booking_deleted'));
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function handleFilters(Request $request, Builder $query): Builder
    {
        $from      = $request->get('from', null);
        $to        = $request->get('to', null);
        $search    = $request->get('search', null);
        $status    = $request->get('status', null);
        $categoryId = $request->get('category_id', null);
        $sort      = $request->get('sort', null);

        // Date range filter (reuses your project's helper)
        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($search)) {
            $query->where('title', 'like', "%{$search}%");
        }

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if (!is_null($status) && $status !== '') {
            if ($status === 'active') {
                $query->where('status', 'published');
            } elseif ($status === 'inactive') {
                $query->where('status', 'draft');
            } else {
                $query->where('status', $status);
            }
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'create_date_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'create_date_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    private function getListsData(Request $request, Builder $query): array|\Illuminate\Http\JsonResponse
    {
        $page  = $request->get('page', 1);
        $count = $this->perPage;
        $total = $query->count();

        $query->limit($count)->offset(($page - 1) * $count);

        $bookings = $query->get();

        if ($request->ajax()) {
            return $this->getAjaxResponse($request, $bookings, $total, $count);
        }

        return [
            'bookings'   => $bookings,
            'pagination' => $this->makePagination($request, $bookings, $total, $count, true),
        ];
    }

    private function getAjaxResponse(Request $request, $bookings, int $total, int $count): \Illuminate\Http\JsonResponse
    {
        $html = '';
        foreach ($bookings as $booking) {
            $html .= (string) view()->make(
                'design_1.panel.bookings.table_item',
                ['booking' => $booking]
            );
        }

        return response()->json([
            'data'       => $html,
            'pagination' => $this->makePagination($request, $bookings, $total, $count, true),
        ]);
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    protected function validateBooking(Request $request, $bookingId = null): array
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255', Rule::unique('bookings', 'slug')->ignore($bookingId)],
            'category_id'      => ['nullable', 'integer', 'exists:booking_categories,id'],
            'description'      => ['nullable', 'string'],
            'price'            => ['nullable', 'numeric', 'min:0'],
            'discount_price'   => ['nullable', 'numeric', 'min:0'],
            'capacity'         => ['nullable', 'integer', 'min:0'],
            'min_persons'      => ['nullable', 'integer', 'min:0'],
            'max_persons'      => ['nullable', 'integer', 'min:0'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'address_line'     => ['nullable', 'string', 'max:255'],
            'city'             => ['nullable', 'string', 'max:120'],
            'state'            => ['nullable', 'string', 'max:120'],
            'country'          => ['nullable', 'string', 'max:120'],
            'postal_code'      => ['nullable', 'string', 'max:120'],
            'lat'              => ['nullable', 'numeric'],
            'lng'              => ['nullable', 'numeric'],
            'meta'             => ['nullable', 'string'],
            'status'           => ['nullable', 'boolean'],
            'featured'         => ['nullable', 'boolean'],
        ]);

        $data['status']   = $request->boolean('status') ? 'published' : 'draft';
        $data['featured'] = $request->boolean('featured');

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']) . '-' . uniqid();
        }

        if (!empty($data['meta'])) {
            try {
                $data['meta'] = json_decode($data['meta'], true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                abort(422, 'Meta field must be valid JSON.');
            }
        } else {
            $data['meta'] = null;
        }

        return $data;
    }

    // ─── Transform ────────────────────────────────────────────────────────────

    protected function transformBooking(Booking $booking): array
    {
        return [
            'id'               => $booking->id,
            'title'            => $booking->title,
            'slug'             => $booking->slug,
            'category_id'      => $booking->category_id,
            'category_name'    => optional($booking->category)->title,
            'description'      => $booking->description,
            'price'            => $booking->price,
            'discount_price'   => $booking->discount_price,
            'capacity'         => $booking->capacity,
            'min_persons'      => $booking->min_persons,
            'max_persons'      => $booking->max_persons,
            'duration_minutes' => $booking->duration_minutes,
            'status'           => $booking->status,
            'featured'         => (bool) $booking->featured,
            'address_line'     => $booking->address_line,
            'city'             => $booking->city,
            'state'            => $booking->state,
            'country'          => $booking->country,
            'postal_code'      => $booking->postal_code,
            'lat'              => $booking->lat,
            'lng'              => $booking->lng,
            'meta'             => $booking->meta,
            'created_at'       => optional($booking->created_at)->toDateTimeString(),
            'updated_at'       => optional($booking->updated_at)->toDateTimeString(),
        ];
    }
}