<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CredentialsData;
use App\Data\TokenPair;
use App\Models\Customer;
use App\Tokens\TokenIssuer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final readonly class IssueToken
{
    public function __construct(private TokenIssuer $tokens) {}

    public function handle(CredentialsData $credentials): TokenPair
    {
        $customer = Customer::query()->where('email', $credentials->email)->first();

        if (! $customer instanceof Customer) {
            // Hash anyway so response time does not reveal which emails exist.
            Hash::make($credentials->password);

            throw $this->invalidCredentials();
        }

        if (! Hash::check($credentials->password, $customer->password)) {
            throw $this->invalidCredentials();
        }

        return $this->tokens->issue($customer);
    }

    private function invalidCredentials(): ValidationException
    {
        return ValidationException::withMessages(['email' => __('auth.failed')]);
    }
}
