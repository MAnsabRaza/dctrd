<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BookingFavorite;

class BookingFavoriteController extends Controller
{
    public function toggle(Request $request, $slug)
    {
        $userId = auth()->id();

        $booking = Booking::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (empty($booking) || empty($userId)) {
            return response()->json(['status' => 'error'], 404);
        }

        $exists = BookingFavorite::where('booking_id', $booking->id)
            ->where('user_id', $userId)
            ->first();

        if (empty($exists)) {
            BookingFavorite::create([
                'user_id' => $userId,
                'booking_id' => $booking->id,
            ]);

            return response()->json(['status' => 'added'], 200);
        }

        $exists->delete();

        return response()->json(['status' => 'removed'], 200);
    }
}
