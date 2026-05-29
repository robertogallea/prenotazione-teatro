<?php

use App\Models\Booking;
use App\Models\Seat;
use App\Models\SeatGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest is redirected to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated user sees Dashboard component', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Dashboard'));
});

test('dashboard props include seats array', function () {
    $user = User::factory()->create();
    Seat::factory()->count(3)->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->has('seats', 3)
            ->has('seats.0.id')
            ->has('seats.0.section')
            ->has('seats.0.block_key')
            ->has('seats.0.row')
            ->has('seats.0.number')
            ->has('seats.0.label')
            ->has('seats.0.booking')
        );
});

test('dashboard props include seat_groups array', function () {
    $user = User::factory()->create();
    SeatGroup::factory()->count(2)->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->has('seat_groups', 2)
            ->has('seat_groups.0.block_key')
            ->has('seat_groups.0.rows')
            ->has('seat_groups.0.seats_per_row')
        );
});

test('seats include booking relation when booked', function () {
    $user = User::factory()->create();
    $seat = Seat::factory()->create();
    Booking::factory()->create(['seat_id' => $seat->id, 'booked_for' => 'Mario Rossi']);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->where('seats.0.booking.booked_for', 'Mario Rossi')
        );
});
