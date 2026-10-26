<?php

declare(strict_types=1);

use App\Models\Customer;
use Database\Factories\CustomerFactory;
use Illuminate\Support\Facades\Http;

it('returns the customer behind the access token without calling the JWKS endpoint over HTTP', function (): void {
    Http::preventStrayRequests();
    $customer = Customer::factory()->create(['name' => 'Ada']);
    $token = $this->postJson('/api/tokens', ['email' => $customer->email, 'password' => CustomerFactory::PASSWORD])
        ->json('data.accessToken');

    $this->getJson('/api/me', ['Authorization' => "Bearer {$token}"])
        ->assertOk()
        ->assertExactJson(['data' => [
            'id' => $customer->id,
            'email' => $customer->email,
            'name' => 'Ada',
            'roles' => ['customer'],
            'registeredAt' => $customer->created_at->toIso8601ZuluString(),
        ]]);
});

it('requires an access token', function (): void {
    $this->getJson('/api/me')->assertUnauthorized();
});
