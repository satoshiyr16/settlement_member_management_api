<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Enums\Common\UserRoleEnum;

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

    /**
     * メンバーロールのユーザーを生成
     */
    public function member(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRoleEnum::MEMBER->value,
        ]);
    }

    /**
     * 管理者ロールのユーザーを生成
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRoleEnum::ADMIN->value,
        ]);
    }
}
