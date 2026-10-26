<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Tokens\KeyRing;
use Illuminate\Http\JsonResponse;

final class ShowJwksController
{
    public function __invoke(KeyRing $keyRing): JsonResponse
    {
        // Make sure there is a key to publish before anyone has signed in.
        $keyRing->signingKey();

        return response()->json(['keys' => $keyRing->jwks()])->header('Cache-Control', 'public, max-age=300');
    }
}
