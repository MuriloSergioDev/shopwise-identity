<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Customer::factory()->admin()->create(['email' => 'admin@shopwise.test', 'name' => 'Shopwise Admin']);
        Customer::factory()->create(['email' => 'customer@shopwise.test', 'name' => 'Ada Customer']);
    }
}
