<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\RegistrationData;
use App\Data\TokenPair;
use App\Enums\Role;
use App\Models\Customer;
use App\Tokens\TokenIssuer;
use Illuminate\Support\Facades\DB;
use Shopwise\Contracts\Events\Identity\CustomerRegisteredV1;
use Shopwise\Kit\Messaging\Outbox;

final readonly class RegisterCustomer
{
    public function __construct(private TokenIssuer $tokens) {}

    public function handle(RegistrationData $data): TokenPair
    {
        return DB::transaction(function () use ($data): TokenPair {
            $customer = Customer::query()->create([
                'email' => $data->email,
                'name' => $data->name,
                'password' => $data->password,
                'roles' => [Role::Customer],
            ]);

            $event = new CustomerRegisteredV1(
                customerId: $customer->id,
                email: $customer->email,
                name: $customer->name,
                registeredAt: $customer->created_at->toIso8601ZuluString(),
            );
            Outbox::record(CustomerRegisteredV1::TYPE, $customer->id, $event->toArray(), CustomerRegisteredV1::SCHEMA);

            return $this->tokens->issue($customer);
        });
    }
}
