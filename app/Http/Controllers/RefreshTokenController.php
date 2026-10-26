<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RotateRefreshToken;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Resources\TokenPairResource;

final class RefreshTokenController
{
    public function __invoke(RefreshTokenRequest $request, RotateRefreshToken $rotateRefreshToken): TokenPairResource
    {
        return TokenPairResource::make($rotateRefreshToken->handle($request->refreshToken()));
    }
}
