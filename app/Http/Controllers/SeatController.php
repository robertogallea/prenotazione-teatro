<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\SeatGroup;
use Inertia\Inertia;
use Inertia\Response;

class SeatController extends Controller
{
    public function index(): Response
    {
        $seats = Seat::with('booking')
            ->orderBy('section')
            ->orderBy('block_key')
            ->orderBy('row')
            ->orderBy('number')
            ->get();

        $seatGroups = SeatGroup::orderBy('section')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Dashboard', [
            'seats' => $seats,
            'seat_groups' => $seatGroups,
        ]);
    }
}
