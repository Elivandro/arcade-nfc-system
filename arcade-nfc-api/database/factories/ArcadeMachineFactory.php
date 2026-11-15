<?php

namespace Database\Factories;

use App\Models\ArcadeMachine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArcadeMachine>
 */
class ArcadeMachineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'  => $this->faker->name(),
            'api_token' => $this->faker->uuid(),
            'is_online' => $this->faker->boolean(80),
            'cost_per_play' => $this->faker->randomFloat(2, 1, 3),
            'last_maintenance' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s')
        ];
    }
}
