<?php

declare(strict_types=1);

namespace App\Data;

final readonly class TokenPair
{
    public function __construct(
        public string $accessToken,
        public int $expiresIn,
        public string $refreshToken,
        public int $refreshExpiresIn,
    ) {}
}
