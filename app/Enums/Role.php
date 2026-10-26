<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Roles carried in the access token's "roles" claim; services check them with `auth.jwt:<role>`.
 */
enum Role: string
{
    case Customer = 'customer';
    case Admin = 'admin';
}
