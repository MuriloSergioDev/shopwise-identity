<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Data\RegistrationData;
use Illuminate\Foundation\Http\FormRequest;

final class RegisterCustomerRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'string', 'min:8', 'max:200'],
            'name' => ['required', 'string', 'max:100'],
        ];
    }

    public function toData(): RegistrationData
    {
        return new RegistrationData(
            email: $this->string('email')->toString(),
            password: $this->string('password')->toString(),
            name: $this->string('name')->trim()->toString(),
        );
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(mb_trim($this->string('email')->toString()))]);
        }
    }
}
