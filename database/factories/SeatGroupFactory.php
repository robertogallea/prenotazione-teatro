<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SeatGroupFactory extends Factory
{
    public function definition(): array
    {
        static $order = 0;

        return [
            'section' => 'platea',
            'block_key' => $this->faker->unique()->lexify('block_???'),
            'label' => $this->faker->words(2, true),
            'sort_order' => ++$order,
            'rows' => [1, 2, 3],
            'seats_per_row' => [4, 2],
            'seat_offset' => 0,
            'has_corridor' => false,
            'corridor_after_slot' => null,
        ];
    }
}
