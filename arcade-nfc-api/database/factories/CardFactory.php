<?php

namespace Database\Factories;

use App\Enums\CardType;
use App\Models\Card;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'uid' => $this->faker->unique()->regexify('[A-F0-9]{8}'),
            'secure_key' => $this->faker->unique()->regexify('[A-F0-9]{32}'),
            'balance' => $this->faker->randomElement([10.00, 100.00, 500.00, 1000.00, 10000.00]),
            'status' => $this->faker->randomElement(CardType::cases()),
        ];
    }
}
