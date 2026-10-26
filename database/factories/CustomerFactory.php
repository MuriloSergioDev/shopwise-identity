<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
final class CustomerFactory extends Factory
{
    public const string PASSWORD = 'correct-horse-battery';

    public function definition(): array
    {
        return [
            'email' => mb_strtolower(fake()->unique()->safeEmail()),
            'name' => fake()->name(),
            'password' => self::PASSWORD,
            'roles' => [Role::Customer],
        ];
    }

    public function admin(): self
    {
        return $this->state(['roles' => [Role::Customer, Role::Admin]]);
    }
}
