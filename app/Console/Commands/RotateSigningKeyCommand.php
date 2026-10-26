<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Tokens\KeyRing;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Retire the current JWT signing key and start signing with a new one.')]
#[Signature('identity:rotate-key')]
final class RotateSigningKeyCommand extends Command
{
    public function handle(KeyRing $keyRing): int
    {
        $key = $keyRing->rotate();
        $this->components->info("Signing with key {$key->kid}; retired keys verify for another ".KeyRing::RETIRED_GRACE_SECONDS.'s.');

        return self::SUCCESS;
    }
}
