<?php

declare(strict_types=1);

use App\Models\Customer;
use Database\Factories\CustomerFactory;

it('issues a token pair for valid credentials', function (): void {
    $customer = Customer::factory()->admin()->create(['email' => 'ada@example.com']);

    $response = $this->postJson('/api/tokens', ['email' => 'Ada@Example.com', 'password' => CustomerFactory::PASSWORD])
        ->assertOk()
        ->assertJsonStructure(['data' => ['tokenType', 'accessToken', 'expiresIn', 'refreshToken', 'refreshExpiresIn']]);

    $claims = verifiedClaims($response->json('data.accessToken'));
    expect($claims->sub)->toBe($customer->id)
        ->and($claims->roles)->toBe(['customer', 'admin']);
});

it('gives the same answer for a wrong password and an unknown email', function (string $email, string $password): void {
    Customer::factory()->create(['email' => 'ada@example.com']);

    $this->postJson('/api/tokens', ['email' => $email, 'password' => $password])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email' => __('auth.failed')]);
})->with([
    'wrong password' => ['ada@example.com', 'wrong-password'],
    'unknown email' => ['nobody@example.com', CustomerFactory::PASSWORD],
]);

it('throttles repeated attempts for the same email', function (): void {
    foreach (range(1, 10) as $attempt) {
        $this->postJson('/api/tokens', ['email' => 'ada@example.com', 'password' => "guess-{$attempt}"])->assertUnprocessable();
    }

    $this->postJson('/api/tokens', ['email' => 'ada@example.com', 'password' => 'guess'])->assertTooManyRequests();
    $this->postJson('/api/tokens', ['email' => 'other@example.com', 'password' => 'guess'])->assertUnprocessable();
});
