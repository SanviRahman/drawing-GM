<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->string('section_key', 80);
            $table->string('heading', 190)->nullable();
            $table->string('subheading', 255)->nullable();
            $table->json('payload');
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['campaign_id', 'is_enabled', 'sort_order'], 'campaign_sections_campaign_enabled_order_idx');
            $table->index(['section_key', 'is_enabled'], 'campaign_sections_key_enabled_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_sections');
    }
};
