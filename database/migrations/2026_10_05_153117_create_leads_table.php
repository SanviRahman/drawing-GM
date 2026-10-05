<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('reference', 50)->unique();
            $table->string('name', 150);
            $table->string('email', 190)->nullable();
            $table->string('phone', 32);

            $table->foreignId('location_id')
                ->nullable()
                ->constrained('locations')
                ->restrictOnDelete();

            $table->text('message')->nullable();
            $table->json('metadata')->nullable();

            $table->string('status', 30)->default('new');

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->string('source_page_url', 255)->nullable();
            $table->json('utm')->nullable();
            $table->json('consent')->nullable();
            $table->json('pricing_snapshot')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('created_at');
            $table->index('assigned_to');
            $table->index('user_id');
            $table->index('location_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
