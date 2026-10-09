<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 190);
            $table->string('slug', 190)->unique();
            $table->text('excerpt')->nullable();
            $table->string('template', 100);
            $table->json('hero_config');
            $table->string('status', 30);
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_homepage')->default(false);
            $table->boolean('show_header')->default(true);
            $table->boolean('show_footer')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->boolean('faq_section_enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index('is_homepage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
