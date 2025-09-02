<?php

namespace Database\Factories;

use App\Enums\GenderEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MemberProfile>
 */
class MemberProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nickname' => fake()->unique()->userName(),
            'gender' => fake()->randomElement(GenderEnum::cases()),
            'birth_date' => fake()->date(),
            'enrollment_date' => fake()->date(),
        ];
    }
}
