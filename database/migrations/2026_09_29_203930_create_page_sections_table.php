<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('section_definition_id')->constrained('section_definitions')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('heading', 190)->nullable();
            $table->string('subheading', 255)->nullable();
            $table->json('payload');
            $table->string('theme', 80)->default('default');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes()->index();

            $table->index(['page_id', 'is_active', 'sort_order']);
            $table->index(['section_definition_id', 'is_active']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};
