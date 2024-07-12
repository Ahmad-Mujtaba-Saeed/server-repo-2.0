<?php

namespace Database\Factories;

use App\Models\GeneratedFee;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GeneratedFee>
 */
class GeneratedFeeFactory extends Factory
{
    protected $model = GeneratedFee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'UsersID' => User::factory(), // Generate a new user or use an existing one
            'Fee' => $this->faker->randomFloat(2, 100, 1000), // Generating a random fee between 100 and 1000
            'Paid' => $this->faker->boolean, // Randomly true or false
            'Date' => $this->faker->date('Y-m-d'), // Generate a random date
            'Role' => 'Student' // Fixed value 'Student'
        ];
    }
}
