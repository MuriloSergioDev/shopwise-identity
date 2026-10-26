<?php

declare(strict_types=1);

namespace App\Tokens;

use App\Data\TokenPair;
use App\Models\Customer;
use App\Models\RefreshToken;
use Firebase\JWT\JWT;
use Illuminate\Support\Str;

/**
 * Issues an access JWT (claims the kit's VerifyJwt expects) plus an opaque, single-use refresh token.
 */
final readonly class TokenIssuer
{
    public const int ACCESS_TTL_SECONDS = 900;

    public const int REFRESH_TTL_SECONDS = 30 * 24 * 3600;

    public function __construct(private KeyRing $keyRing) {}

    /**
     * @param  string|null  $familyId  family of the refresh token being rotated; null starts a new sign-in
     */
    public function issue(Customer $customer, ?string $familyId = null): TokenPair
    {
        $key = $this->keyRing->signingKey();
        $now = now()->toImmutable();

        $accessToken = JWT::encode([
            'iss' => config()->string('shopwise-auth.issuer'),
            'sub' => $customer->id,
            'email' => $customer->email,
            'roles' => $customer->roleNames(),
            'iat' => $now->getTimestamp(),
            'exp' => $now->addSeconds(self::ACCESS_TTL_SECONDS)->getTimestamp(),
            'jti' => (string) Str::ulid(),
        ], $key->private_key, 'RS256', $key->kid);

        $refreshToken = Str::random(64);
        RefreshToken::query()->create([
            'customer_id' => $customer->id,
            'family_id' => $familyId ?? Str::lower((string) Str::ulid()),
            'token_hash' => RefreshToken::hash($refreshToken),
            'expires_at' => $now->addSeconds(self::REFRESH_TTL_SECONDS),
            'created_at' => $now,
        ]);

        return new TokenPair(
            accessToken: $accessToken,
            expiresIn: self::ACCESS_TTL_SECONDS,
            refreshToken: $refreshToken,
            refreshExpiresIn: self::REFRESH_TTL_SECONDS,
        );
    }
}
