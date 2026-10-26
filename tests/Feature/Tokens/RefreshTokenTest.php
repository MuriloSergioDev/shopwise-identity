<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\RefreshToken;
use Database\Factories\CustomerFactory;

beforeEach(function (): void {
    $this->customer = Customer::factory()->create();
    $this->signIn = fn (): array => $this->postJson('/api/tokens', [
        'email' => $this->customer->email,
        'password' => CustomerFactory::PASSWORD,
    ])->assertOk()->json('data');
});

it('rotates a refresh token into a new pair', function (): void {
    $first = ($this->signIn)();

    $second = $this->postJson('/api/tokens/refresh', ['refreshToken' => $first['refreshToken']])->assertOk()->json('data');

    expect($second['refreshToken'])->not->toBe($first['refreshToken'])
        ->and(verifiedClaims($second['accessToken'])->sub)->toBe($this->customer->id)
        ->and(RefreshToken::query()->whereNotNull('used_at')->count())->toBe(1);
});

it('revokes the whole family when a spent refresh token is reused', function (): void {
    $first = ($this->signIn)();
    $second = $this->postJson('/api/tokens/refresh', ['refreshToken' => $first['refreshToken']])->json('data');

    $this->postJson('/api/tokens/refresh', ['refreshToken' => $first['refreshToken']])->assertUnauthorized();
    $this->postJson('/api/tokens/refresh', ['refreshToken' => $second['refreshToken']])->assertUnauthorized();
});

it('keeps other sign-ins alive when one is revoked', function (): void {
    $phone = ($this->signIn)();
    $laptop = ($this->signIn)();

    $this->postJson('/api/tokens/revoke', ['refreshToken' => $phone['refreshToken']])->assertNoContent();

    $this->postJson('/api/tokens/refresh', ['refreshToken' => $phone['refreshToken']])->assertUnauthorized();
    $this->postJson('/api/tokens/refresh', ['refreshToken' => $laptop['refreshToken']])->assertOk();
});

it('rejects expired and unknown refresh tokens', function (): void {
    $tokens = ($this->signIn)();
    $this->travel(31)->days();

    $this->postJson('/api/tokens/refresh', ['refreshToken' => $tokens['refreshToken']])->assertUnauthorized();
    $this->postJson('/api/tokens/refresh', ['refreshToken' => 'unknown'])->assertUnauthorized();
});

it('accepts revoking an unknown token without revealing anything', function (): void {
    $this->postJson('/api/tokens/revoke', ['refreshToken' => 'unknown'])->assertNoContent();
});
