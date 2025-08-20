<?php

namespace Database\Factories;

use App\Enums\Common\GenderEnum;
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
            'gender' => fake()->randomElement([1, 2, 3, 4]), // GenderEnumの値を使用
            'birth_date' => fake()->date(),
            'enrollment_date' => fake()->date(),
        ];
    }
}
