<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shape defined in shopwise-contracts/openapi/identity.yaml (components.schemas.Customer).
 *
 * @mixin Customer
 */
final class CustomerResource extends JsonResource
{
    /**
     * @return array{id: string, email: string, name: string, roles: list<string>, registeredAt: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'roles' => $this->roleNames(),
            'registeredAt' => $this->created_at->toIso8601ZuluString(),
        ];
    }
}
