<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\Customer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Grant a role to a customer. Takes effect on their next token.')]
#[Signature('identity:grant-role {email} {role : customer|admin}')]
final class GrantRoleCommand extends Command
{
    public function handle(): int
    {
        $role = Role::tryFrom($this->argument('role'));
        $customer = Customer::query()->where('email', mb_strtolower($this->argument('email')))->first();

        if ($role === null || $customer === null) {
            $this->components->error('Unknown role or customer.');

            return self::FAILURE;
        }

        if (! $customer->roles->contains($role)) {
            $customer->update(['roles' => $customer->roles->push($role)]);
        }

        $this->components->info("{$customer->email}: ".implode(', ', $customer->roleNames()));

        return self::SUCCESS;
    }
}
