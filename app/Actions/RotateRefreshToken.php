<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\TokenPair;
use App\Models\RefreshToken;
use App\Tokens\TokenIssuer;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;

/**
 * Spends a refresh token for a new pair in the same family. Presenting an already spent token means
 * two parties hold it (one of them stole it), so the whole family is revoked and both must sign in again.
 */
final readonly class RotateRefreshToken
{
    public function __construct(private TokenIssuer $tokens) {}

    public function handle(string $refreshToken): TokenPair
    {
        // The transaction returns instead of throwing so a family revocation is committed, not rolled back.
        $pair = DB::transaction(function () use ($refreshToken): ?TokenPair {
            $current = RefreshToken::query()
                ->where('token_hash', RefreshToken::hash($refreshToken))
                ->lockForUpdate()
                ->first();

            if ($current === null || $current->revoked_at !== null || $current->expires_at->isPast()) {
                return null;
            }

            if ($current->used_at !== null) {
                RefreshToken::revokeFamily($current->family_id);

                return null;
            }

            $current->update(['used_at' => now()]);

            return $this->tokens->issue($current->customer, $current->family_id);
        });

        if ($pair === null) {
            throw new AuthenticationException('Invalid refresh token.');
        }

        return $pair;
    }
}
