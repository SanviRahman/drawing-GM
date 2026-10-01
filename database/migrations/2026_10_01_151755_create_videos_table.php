<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('attachable');
            $table->string('title', 190);
            $table->text('caption')->nullable();
            $table->string('source_type', 30);
            $table->string('provider', 50)->nullable();
            $table->string('provider_video_id', 190)->nullable();
            $table->string('source_url', 2048)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->boolean('autoplay')->default(false);
            $table->boolean('muted')->default(false);
            $table->boolean('controls')->default(true);
            $table->boolean('loop')->default(false);
            $table->string('processing_status', 30)->default('ready');
            $table->text('processing_error')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['source_type', 'is_active', 'sort_order']);
            $table->index('processing_status');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
