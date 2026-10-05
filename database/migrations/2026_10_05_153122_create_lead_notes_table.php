<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->string('author_type')->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->text('note');
            $table->boolean('visible_to_user')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['lead_id', 'created_at'], 'lead_notes_lead_created_idx');
            $table->index(['author_type', 'author_id'], 'lead_notes_author_idx');
            $table->index(['lead_id', 'visible_to_user'], 'lead_notes_visibility_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_notes');
    }
};
