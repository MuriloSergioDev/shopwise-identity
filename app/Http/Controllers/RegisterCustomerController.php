<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RegisterCustomer;
use App\Http\Requests\RegisterCustomerRequest;
use App\Http\Resources\TokenPairResource;
use Illuminate\Http\JsonResponse;

final class RegisterCustomerController
{
    public function __invoke(RegisterCustomerRequest $request, RegisterCustomer $registerCustomer): JsonResponse
    {
        $tokens = $registerCustomer->handle($request->toData());

        return TokenPairResource::make($tokens)->response()->setStatusCode(201);
    }
}
