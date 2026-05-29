<?php

use App\Models\Booking;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest cannot book a seat', function () {
    $seat = Seat::factory()->create();

    $this->post('/bookings', ['seat_ids' => [$seat->id], 'booked_for' => 'Mario Rossi'])
        ->assertRedirect('/login');
});

test('admin can book an available seat', function () {
    $user = User::factory()->create();
    $seat = Seat::factory()->create();

    $this->actingAs($user)
        ->post('/bookings', [
            'seat_ids' => [$seat->id],
            'booked_for' => 'Mario Rossi',
        ])
        ->assertRedirect();

    expect(Booking::where('seat_id', $seat->id)->where('booked_for', 'Mario Rossi')->exists())->toBeTrue();
});

test('admin can book multiple seats at once', function () {
    $user = User::factory()->create();
    $seats = Seat::factory()->count(3)->create();

    $this->actingAs($user)
        ->post('/bookings', [
            'seat_ids' => $seats->pluck('id')->toArray(),
            'booked_for' => 'Gruppo Scolastico',
        ])
        ->assertRedirect();

    expect(Booking::where('booked_for', 'Gruppo Scolastico')->count())->toBe(3);
});

test('booking fails when seat is already taken', function () {
    $user = User::factory()->create();
    $seat = Seat::factory()->create();
    Booking::factory()->create(['seat_id' => $seat->id]);

    $this->actingAs($user)
        ->post('/bookings', [
            'seat_ids' => [$seat->id],
            'booked_for' => 'Luigi Verdi',
        ])
        ->assertSessionHasErrors('seat_ids');

    expect(Booking::where('booked_for', 'Luigi Verdi')->exists())->toBeFalse();
});

test('booking requires booked_for field', function () {
    $user = User::factory()->create();
    $seat = Seat::factory()->create();

    $this->actingAs($user)
        ->post('/bookings', ['seat_ids' => [$seat->id]])
        ->assertSessionHasErrors('booked_for');
});

test('booking requires at least one seat', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/bookings', ['seat_ids' => [], 'booked_for' => 'Mario'])
        ->assertSessionHasErrors('seat_ids');
});

test('admin can cancel a booking', function () {
    $user = User::factory()->create();
    $booking = Booking::factory()->create();

    $this->actingAs($user)
        ->delete("/bookings/{$booking->id}")
        ->assertRedirect();

    expect(Booking::find($booking->id))->toBeNull();
});

test('guest cannot cancel a booking', function () {
    $booking = Booking::factory()->create();

    $this->delete("/bookings/{$booking->id}")
        ->assertRedirect('/login');

    expect(Booking::find($booking->id))->not->toBeNull();
});
