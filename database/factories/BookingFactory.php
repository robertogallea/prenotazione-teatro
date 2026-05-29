<?php

namespace Database\Factories;

use App\Models\Seat;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'seat_id' => Seat::factory(),
            'booked_for' => $this->faker->name(),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }
}
