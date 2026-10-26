<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Body of both /tokens/refresh and /tokens/revoke.
 */
final class RefreshTokenRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'refreshToken' => ['required', 'string', 'max:200'],
        ];
    }

    public function refreshToken(): string
    {
        return $this->string('refreshToken')->toString();
    }
}
