<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Customer;

it('grants a role once', function (): void {
    $customer = Customer::factory()->create(['email' => 'ada@example.com']);

    $this->artisan('identity:grant-role', ['email' => 'ADA@example.com', 'role' => 'admin'])->assertSuccessful();
    $this->artisan('identity:grant-role', ['email' => 'ada@example.com', 'role' => 'admin'])->assertSuccessful();

    expect($customer->refresh()->roles->all())->toBe([Role::Customer, Role::Admin]);
});

it('fails for unknown customers and roles', function (array $arguments): void {
    Customer::factory()->create(['email' => 'ada@example.com']);

    $this->artisan('identity:grant-role', $arguments)->assertFailed();
})->with([
    'unknown customer' => [['email' => 'nobody@example.com', 'role' => 'admin']],
    'unknown role' => [['email' => 'ada@example.com', 'role' => 'root']],
]);
