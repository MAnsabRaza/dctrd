<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
   public function index()
    {
        $this->authorize('admin_booking');

        removeContentLocale();

        $booking = Booking::orderBy('order')->get();
        $data = [
            'pageTitle'         => trans('admin/main.booking'),
            'bookingCategories' => $booking,
        ];
        return view('admin.booking.booking', $data);
    }
}
