<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $customer_id
 * @property string $family_id
 * @property string $token_hash
 * @property Carbon $expires_at
 * @property Carbon|null $used_at
 * @property Carbon|null $revoked_at
 * @property Carbon $created_at
 * @property-read Customer $customer
 */
#[Fillable(['customer_id', 'family_id', 'token_hash', 'expires_at', 'used_at', 'revoked_at', 'created_at'])]
#[WithoutTimestamps]
final class RefreshToken extends Model
{
    use HasUlids;

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function revokeFamily(string $familyId): void
    {
        self::query()->where('family_id', $familyId)->whereNull('revoked_at')->update(['revoked_at' => now()]);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'immutable_datetime',
            'used_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }
}
