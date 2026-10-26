<?php

declare(strict_types=1);

namespace App\Tokens;

use App\Models\SigningKey;
use Firebase\JWT\Key;
use Shopwise\Kit\Auth\KeySource;

/**
 * Lets the kit's `auth.jwt` middleware verify tokens against identity's own keys, so identity
 * never calls its own JWKS endpoint over HTTP.
 */
final readonly class DatabaseKeySource implements KeySource
{
    public function __construct(private KeyRing $keyRing) {}

    public function keys(bool $refresh = false): array
    {
        return $this->keyRing->verificationKeys()
            ->mapWithKeys(fn (SigningKey $key): array => [$key->kid => new Key($key->public_key, 'RS256')])
            ->all();
    }
}
