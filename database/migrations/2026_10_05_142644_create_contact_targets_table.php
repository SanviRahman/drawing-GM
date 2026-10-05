<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_targets', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('contact_channel_id')->constrained('contact_channels')->restrictOnDelete();
            $table->string('targetable_type');
            $table->unsignedBigInteger('targetable_id');
            $table->timestamps();

            // Project requirement: admin-managed mapping rows must be recoverable.
            $table->softDeletes();
            $table->unique(['contact_channel_id', 'targetable_type', 'targetable_id'],'contact_targets_channel_target_unique');
            $table->index(['targetable_type', 'targetable_id'],'contact_targets_targetable_idx');
            $table->index(['contact_channel_id', 'deleted_at'],'contact_targets_channel_deleted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_targets');
    }
};
