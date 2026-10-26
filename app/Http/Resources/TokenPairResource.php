<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Data\TokenPair;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shape defined in shopwise-contracts/openapi/identity.yaml (components.schemas.TokenPair).
 *
 * @property TokenPair $resource
 */
final class TokenPairResource extends JsonResource
{
    /**
     * @return array{tokenType: string, accessToken: string, expiresIn: int, refreshToken: string, refreshExpiresIn: int}
     */
    public function toArray(Request $request): array
    {
        return [
            'tokenType' => 'Bearer',
            'accessToken' => $this->resource->accessToken,
            'expiresIn' => $this->resource->expiresIn,
            'refreshToken' => $this->resource->refreshToken,
            'refreshExpiresIn' => $this->resource->refreshExpiresIn,
        ];
    }
}
