<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 190);
            $table->string('slug', 190)->unique();
            $table->string('custom_route', 255)->nullable()->unique();
            $table->text('summary')->nullable();
            $table->string('status', 30)->default('draft');
            $table->json('hero_config');
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'starts_at', 'ends_at'], 'campaigns_public_schedule_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
