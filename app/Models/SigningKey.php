<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * RSA key pair that signs access tokens. The newest non-retired key signs; retired keys stay in the
 * JWKS for a grace period so tokens they signed still verify.
 *
 * @property string $kid
 * @property string $private_key PEM, encrypted at rest
 * @property string $public_key PEM
 * @property Carbon $activated_at
 * @property Carbon|null $retired_at
 */
#[Fillable(['kid', 'private_key', 'public_key', 'activated_at', 'retired_at'])]
#[Hidden(['private_key'])]
#[WithoutTimestamps]
#[WithoutIncrementing]
final class SigningKey extends Model
{
    protected $primaryKey = 'kid';

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'private_key' => 'encrypted',
            'activated_at' => 'immutable_datetime',
            'retired_at' => 'immutable_datetime',
        ];
    }
}
