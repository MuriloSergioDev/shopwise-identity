<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\IssueToken;
use App\Http\Requests\IssueTokenRequest;
use App\Http\Resources\TokenPairResource;

final class IssueTokenController
{
    public function __invoke(IssueTokenRequest $request, IssueToken $issueToken): TokenPairResource
    {
        return TokenPairResource::make($issueToken->handle($request->toData()));
    }
}
