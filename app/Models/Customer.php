<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property string $email
 * @property string $name
 * @property string $password
 * @property Collection<int, Role> $roles
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['email', 'name', 'password', 'roles'])]
#[Hidden(['password'])]
final class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    use HasUlids;

    /**
     * @return list<string>
     */
    public function roleNames(): array
    {
        return array_values($this->roles->map(fn (Role $role): string => $role->value)->all());
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'roles' => AsEnumCollection::of(Role::class),
        ];
    }
}
