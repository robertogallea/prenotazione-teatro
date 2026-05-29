<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'seat_ids' => ['required', 'array', 'min:1'],
            'seat_ids.*' => ['required', 'integer', 'exists:seats,id'],
            'booked_for' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (Booking::whereIn('seat_id', $validated['seat_ids'])->exists()) {
            return back()->withErrors(['seat_ids' => 'Uno o più posti selezionati sono già prenotati.']);
        }

        DB::transaction(function () use ($validated): void {
            foreach ($validated['seat_ids'] as $seatId) {
                Booking::create([
                    'seat_id' => $seatId,
                    'booked_for' => $validated['booked_for'],
                    'notes' => $validated['notes'] ?? null,
                ]);
            }
        });

        return back();
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->delete();

        return back();
    }
}
