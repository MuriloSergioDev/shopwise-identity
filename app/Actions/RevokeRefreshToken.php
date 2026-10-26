<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\RefreshToken;

/**
 * Sign-out: revokes every refresh token from the same sign-in. Unknown tokens are ignored.
 */
final readonly class RevokeRefreshToken
{
    public function handle(string $refreshToken): void
    {
        $familyId = RefreshToken::query()->where('token_hash', RefreshToken::hash($refreshToken))->value('family_id');

        if (is_string($familyId)) {
            RefreshToken::revokeFamily($familyId);
        }
    }
}
