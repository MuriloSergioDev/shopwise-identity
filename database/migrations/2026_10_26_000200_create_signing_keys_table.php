<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signing_keys', function (Blueprint $table): void {
            $table->string('kid', 40)->primary();
            // Encrypted with APP_KEY (model cast).
            $table->text('private_key');
            $table->text('public_key');
            $table->timestamp('activated_at');
            $table->timestamp('retired_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signing_keys');
    }
};
