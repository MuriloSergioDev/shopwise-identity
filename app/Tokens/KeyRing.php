<?php

declare(strict_types=1);

namespace App\Tokens;

use App\Models\SigningKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * The RSA keys that sign and verify access tokens. A key is created on first use; `identity:rotate-key`
 * retires the current one. Retired keys keep verifying for a grace period that outlives every token
 * they signed plus the JWKS cache in other services.
 */
final readonly class KeyRing
{
    public const int RETIRED_GRACE_SECONDS = 3600;

    public function signingKey(): SigningKey
    {
        return SigningKey::query()->whereNull('retired_at')->latest('activated_at')->first() ?? $this->rotate();
    }

    public function rotate(): SigningKey
    {
        $pair = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        if ($pair === false || ! openssl_pkey_export($pair, $privatePem) || ! is_string($privatePem)) {
            throw new RuntimeException('Could not generate an RSA key pair.');
        }

        $details = openssl_pkey_get_details($pair);
        if ($details === false || ! is_string($details['key'] ?? null)) {
            throw new RuntimeException('Could not read the RSA public key.');
        }

        return DB::transaction(function () use ($privatePem, $details): SigningKey {
            SigningKey::query()->whereNull('retired_at')->update(['retired_at' => now()]);

            return SigningKey::query()->create([
                'kid' => Str::lower((string) Str::ulid()),
                'private_key' => $privatePem,
                'public_key' => $details['key'],
                'activated_at' => now(),
            ]);
        });
    }

    /**
     * @return Collection<int, SigningKey>
     */
    public function verificationKeys(): Collection
    {
        return SigningKey::query()
            ->where(fn (Builder $query) => $query
                ->whereNull('retired_at')
                ->orWhere('retired_at', '>', now()->subSeconds(self::RETIRED_GRACE_SECONDS)))
            ->latest('activated_at')
            ->get();
    }

    /**
     * @return list<array{kty: string, kid: string, use: string, alg: string, n: string, e: string}>
     */
    public function jwks(): array
    {
        return array_values($this->verificationKeys()->map(function (SigningKey $key): array {
            $public = openssl_pkey_get_public($key->public_key);
            $details = $public === false ? false : openssl_pkey_get_details($public);
            $rsa = is_array($details) ? ($details['rsa'] ?? null) : null;
            if (! is_array($rsa) || ! is_string($rsa['n'] ?? null) || ! is_string($rsa['e'] ?? null)) {
                throw new RuntimeException("Signing key {$key->kid} is not a valid RSA public key.");
            }

            return [
                'kty' => 'RSA',
                'kid' => $key->kid,
                'use' => 'sig',
                'alg' => 'RS256',
                'n' => $this->base64Url($rsa['n']),
                'e' => $this->base64Url($rsa['e']),
            ];
        })->all());
    }

    private function base64Url(string $bytes): string
    {
        return mb_rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
