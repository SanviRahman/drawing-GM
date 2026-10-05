<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table): void {
            $table->id();
            // 500 keeps the unique utf8mb4 index inside MySQL/InnoDB limits.
            $table->string('from_path', 500)->unique();
            $table->string('to_url', 2048);
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->unsignedBigInteger('hits')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_active', 'from_path']);
            $table->index('last_hit_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
