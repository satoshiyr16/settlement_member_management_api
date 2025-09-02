<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserRoleEnum;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password123'),
            'role' => UserRoleEnum::MEMBER->value,
            'suspended_at' => null,
        ];
    }

    public function member(): static
    {
        return $this->state(fn(array $_) => [
            'role' => UserRoleEnum::MEMBER->value,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn(array $_) => [
            'role' => UserRoleEnum::ADMIN->value,
        ]);
    }
}
