<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_metas', function (Blueprint $table): void {
            $table->id();
            $table->string('seoable_type');
            $table->unsignedBigInteger('seoable_id');
            $table->string('meta_title', 190)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url', 2048)->nullable();
            $table->string('robots', 100)->default('index,follow');
            $table->string('og_title', 190)->nullable();
            $table->text('og_description')->nullable();
            $table->json('schema_overrides')->nullable();
            $table->boolean('include_in_sitemap')->default(true);
            $table->decimal('sitemap_priority', 2, 1)->nullable();
            $table->string('sitemap_changefreq', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['seoable_type', 'seoable_id'], 'seo_metas_owner_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};
