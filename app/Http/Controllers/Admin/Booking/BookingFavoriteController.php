<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingFavorite;
use App\Models\Booking;

class BookingFavoriteController extends Controller
{
    // ─────────────────────────────────────────────
    // LIST
    // ─────────────────────────────────────────────
    public function index()
    {
        $favorites = BookingFavorite::with(['user', 'booking'])
            ->latest()
            ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get();

        return view('admin.booking.favorite', [
            'pageTitle' => 'Booking Favorites',
            'favorites' => $favorites,
            'bookings' => $bookings,
            'editFavorite' => null,
        ]);
    }

    // ─────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $validated['user_id'] = auth()->id();

        // prevent duplicate
        $exists = BookingFavorite::where('user_id', auth()->id())
            ->where('booking_id', $request->booking_id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'booking_id' => 'Favorite already exists.'
            ]);
        }

        $favorite = BookingFavorite::create($validated);

        $this->sendFavoriteNotification($favorite);

        return redirect(getAdminPanelUrl('/booking/favorite'))
            ->with('success', 'Favorite created successfully.');
    }

    // ─────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────
    public function edit($id)
    {
        $editFavorite = BookingFavorite::findOrFail($id);

        $favorites = BookingFavorite::with(['user', 'booking'])
            ->latest()
            ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get();

        return view('admin.booking.favorite', [
            'pageTitle' => 'Edit Favorite',
            'favorites' => $favorites,
            'bookings' => $bookings,
            'editFavorite' => $editFavorite,
        ]);
    }

    // ─────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $favorite = BookingFavorite::findOrFail($id);

        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $validated['user_id'] = auth()->id();

        $favorite->update($validated);

        return redirect(getAdminPanelUrl('/booking/favorite'))
            ->with('success', 'Favorite updated successfully.');
    }

    // ─────────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────────
    public function delete($id)
    {
        $favorite = BookingFavorite::findOrFail($id);

        $favorite->delete();

        return redirect(getAdminPanelUrl('/booking/favorite'))
            ->with('success', 'Favorite deleted successfully.');
    }

    private function sendFavoriteNotification(BookingFavorite $favorite): void
    {
        $favorite->loadMissing(['booking', 'user']);

        if (empty($favorite->booking) || empty($favorite->booking->creator_id)) {
            return;
        }

        $notifyOptions = [
            '[c.title]' => $favorite->booking->title,
            '[item_title]' => $favorite->booking->title,
            '[u.name]' => optional($favorite->user)->full_name,
        ];

        sendNotification('booking_new_favorite', $notifyOptions, $favorite->booking->creator_id);
    }
}
