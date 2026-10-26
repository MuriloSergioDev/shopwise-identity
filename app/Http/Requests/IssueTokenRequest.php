<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Data\CredentialsData;
use Illuminate\Foundation\Http\FormRequest;

final class IssueTokenRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:200'],
        ];
    }

    public function toData(): CredentialsData
    {
        return new CredentialsData(
            email: mb_strtolower(mb_trim($this->string('email')->toString())),
            password: $this->string('password')->toString(),
        );
    }
}
