<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Shopwise\Kit\Auth\JwtUser;

final class ShowCurrentCustomerController
{
    public function __invoke(Request $request): CustomerResource
    {
        $user = $request->user();
        abort_unless($user instanceof JwtUser, 401);

        return CustomerResource::make(Customer::query()->findOrFail($user->id));
    }
}
