<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\SigningKey;
use App\Tokens\KeyRing;
use Database\Factories\CustomerFactory;

it('publishes one RSA key before anyone signs in', function (): void {
    $this->getJson('/.well-known/jwks.json')
        ->assertOk()
        ->assertHeader('Cache-Control', 'max-age=300, public')
        ->assertJsonCount(1, 'keys')
        ->assertJsonPath('keys.0.kty', 'RSA')
        ->assertJsonPath('keys.0.alg', 'RS256')
        ->assertJsonPath('keys.0.use', 'sig');

    expect(SigningKey::query()->sole()->getRawOriginal('private_key'))->not->toContain('PRIVATE KEY');
});

it('keeps verifying tokens signed with a rotated key until the grace period ends', function (): void {
    $customer = Customer::factory()->create();
    $token = $this->postJson('/api/tokens', ['email' => $customer->email, 'password' => CustomerFactory::PASSWORD])
        ->json('data.accessToken');

    $this->artisan('identity:rotate-key')->assertSuccessful();

    $this->getJson('/.well-known/jwks.json')->assertJsonCount(2, 'keys');
    $this->getJson('/api/me', ['Authorization' => "Bearer {$token}"])->assertOk();

    $this->travel(KeyRing::RETIRED_GRACE_SECONDS + 1)->seconds();
    $this->getJson('/.well-known/jwks.json')->assertJsonCount(1, 'keys');
});

it('signs new tokens with the newest key', function (): void {
    $customer = Customer::factory()->create();
    $this->artisan('identity:rotate-key')->assertSuccessful();
    $this->artisan('identity:rotate-key')->assertSuccessful();

    $token = $this->postJson('/api/tokens', ['email' => $customer->email, 'password' => CustomerFactory::PASSWORD])
        ->json('data.accessToken');
    $header = json_decode(base64_decode(explode('.', (string) $token)[0]), true);

    expect($header['kid'])->toBe(SigningKey::query()->whereNull('retired_at')->sole()->kid);
});
