<?php

declare(strict_types=1);

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Shopwise\Kit\Messaging\Envelope;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature');

/**
 * Loads the contract example envelope for an event (shopwise-contracts/examples), optionally overriding data fields.
 *
 * @param  array<string, mixed>  $data
 */
function contractExample(string $typeAndVersion, array $data = []): Envelope
{
    $json = file_get_contents(base_path("vendor/shopwise/contracts/examples/{$typeAndVersion}.json"));
    /** @var array<string, mixed> $payload */
    $payload = json_decode((string) $json, true, flags: JSON_THROW_ON_ERROR);
    /** @var array<string, mixed> $original */
    $original = $payload['data'];
    $payload['data'] = array_replace($original, $data);

    return Envelope::fromArray($payload);
}

/**
 * Verifies an access token the way other services do: against identity's published JWKS.
 */
function verifiedClaims(string $accessToken): stdClass
{
    /** @var array{keys: list<array<string, mixed>>} $jwks */
    $jwks = test()->getJson('/.well-known/jwks.json')->assertOk()->json();

    return JWT::decode($accessToken, JWK::parseKeySet($jwks, 'RS256'));
}
