<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_providers', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 30)->unique();
            $table->string('public_identifier', 255)->nullable();
            // Encrypted secret/token storage; kept non-TEXT so it remains a secure password field, not a rich-text editor.
            $table->string('secret', 2048)->nullable();
            // EncryptedJson stores a valid JSON envelope containing encrypted ciphertext.
            $table->json('config')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->boolean('test_mode')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_enabled', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_providers');
    }
};
