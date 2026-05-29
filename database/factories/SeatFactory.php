<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SeatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'section' => $this->faker->randomElement(['platea', 'galleria']),
            'block_key' => $this->faker->randomElement(['balconata', 'left', 'center', 'right', 'gallery']),
            'row' => $this->faker->randomElement(['A', 'B', '10', '20', 'F1g']),
            'number' => $this->faker->numberBetween(1, 18),
            'label' => $this->faker->bothify('??##'),
        ];
    }
}
