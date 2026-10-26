<?php

declare(strict_types=1);

use App\Models\Customer;
use Shopwise\Kit\Messaging\OutboxMessage;

it('registers a customer, signs them in and records identity.customer-registered', function (): void {
    $response = $this->postJson('/api/customers', [
        'email' => '  Ada@Example.com ',
        'password' => 'correct-horse-battery',
        'name' => 'Ada Lovelace',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.tokenType', 'Bearer')
        ->assertJsonPath('data.expiresIn', 900);

    $customer = Customer::query()->sole();
    $outbox = OutboxMessage::query()->sole();
    $claims = verifiedClaims($response->json('data.accessToken'));

    expect($customer->email)->toBe('ada@example.com')
        ->and($customer->password)->not->toBe('correct-horse-battery')
        ->and($claims->iss)->toBe('shopwise-identity')
        ->and($claims->sub)->toBe($customer->id)
        ->and($claims->email)->toBe('ada@example.com')
        ->and($claims->roles)->toBe(['customer'])
        ->and($outbox->type)->toBe('identity.customer-registered')
        ->and($outbox->subject)->toBe($customer->id)
        // toEqual: Postgres jsonb does not keep key order.
        ->and($outbox->envelope['data'])->toEqual([
            'customerId' => $customer->id,
            'email' => 'ada@example.com',
            'name' => 'Ada Lovelace',
            'registeredAt' => $customer->created_at->toIso8601ZuluString(),
        ]);
});

it('rejects an email that is already registered, whatever its case', function (): void {
    Customer::factory()->create(['email' => 'ada@example.com']);

    $this->postJson('/api/customers', ['email' => 'ADA@example.com', 'password' => 'correct-horse-battery', 'name' => 'Ada'])
        ->assertUnprocessable()->assertJsonValidationErrors('email');

    expect(OutboxMessage::query()->count())->toBe(0);
});

it('rejects invalid registrations', function (array $body, string $field): void {
    $valid = ['email' => 'ada@example.com', 'password' => 'correct-horse-battery', 'name' => 'Ada'];

    $this->postJson('/api/customers', array_replace($valid, $body))
        ->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    'bad email' => [['email' => 'not-an-email'], 'email'],
    'short password' => [['password' => 'short'], 'password'],
    'missing name' => [['name' => ''], 'name'],
    'long name' => [['name' => str_repeat('a', 101)], 'name'],
]);
