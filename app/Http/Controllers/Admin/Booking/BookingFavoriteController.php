<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingFavorite;
use App\Models\Booking;
use App\User;

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

        $users = User::orderBy('id', 'desc')->get();
        $bookings = Booking::orderBy('id', 'desc')->get();

        return view('admin.booking.favorite', [
            'pageTitle' => 'Booking Favorites',
            'favorites' => $favorites,
            'users' => $users,
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
            'user_id' => 'required|exists:users,id',
            'booking_id' => 'required|exists:bookings,id',
        ]);

        // prevent duplicate
        $exists = BookingFavorite::where('user_id', $request->user_id)
            ->where('booking_id', $request->booking_id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'booking_id' => 'Favorite already exists.'
            ]);
        }

        BookingFavorite::create($validated);

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

        $users = User::orderBy('id', 'desc')->get();
        $bookings = Booking::orderBy('id', 'desc')->get();

        return view('admin.booking.favorite', [
            'pageTitle' => 'Edit Favorite',
            'favorites' => $favorites,
            'users' => $users,
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
            'user_id' => 'required|exists:users,id',
            'booking_id' => 'required|exists:bookings,id',
        ]);

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
}