<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_section_id')->constrained('page_sections')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('media_id')->constrained('media')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('role', 80);
            $table->text('caption_override')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes()->index();

            $table->unique(['page_section_id', 'media_id', 'role'], 'section_media_unique_assignment');
            $table->index(['page_section_id', 'role', 'sort_order'], 'section_media_render_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_media');
    }
};
