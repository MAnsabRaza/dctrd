<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingComment;
use App\Models\Booking;

class BookingCommentController extends Controller
{
    // ─────────────────────────────────────────────
    // LIST
    // ─────────────────────────────────────────────
    public function index()
    {
        $comments = BookingComment::with(['user', 'booking'])
            ->latest()
            ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get();

        return view('admin.booking.comment', [
            'pageTitle' => 'Booking Comments',
            'comments' => $comments,
            'bookings' => $bookings,
            'editComment' => null,
        ]);
    }

    // ─────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->merge([
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'comment' => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        $validated['user_id'] = auth()->id();

        BookingComment::create($validated);

        return redirect(getAdminPanelUrl('/booking/comment'))
            ->with('success', 'Comment created successfully.');
    }

    // ─────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────
    public function edit($id)
    {
        $editComment = BookingComment::findOrFail($id);

        $comments = BookingComment::with(['user', 'booking'])
            ->latest()
            ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get();

        return view('admin.booking.comment', [
            'pageTitle' => 'Edit Comment',
            'comments' => $comments,
            'bookings' => $bookings,
            'editComment' => $editComment,
        ]);
    }

    // ─────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $comment = BookingComment::findOrFail($id);

        $request->merge([
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'comment' => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        $validated['user_id'] = auth()->id();

        $comment->update($validated);

        return redirect(getAdminPanelUrl('/booking/comment'))
            ->with('success', 'Comment updated successfully.');
    }

    // ─────────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────────
    public function delete($id)
    {
        $comment = BookingComment::findOrFail($id);

        $comment->delete();

        return redirect(getAdminPanelUrl('/booking/comment'))
            ->with('success', 'Comment deleted successfully.');
    }
}