<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RevokeRefreshToken;
use App\Http\Requests\RefreshTokenRequest;
use Illuminate\Http\Response;

final class RevokeTokenController
{
    public function __invoke(RefreshTokenRequest $request, RevokeRefreshToken $revokeRefreshToken): Response
    {
        $revokeRefreshToken->handle($request->refreshToken());

        return response()->noContent();
    }
}
