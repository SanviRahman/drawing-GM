<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->string('changed_by_type')->nullable();
            $table->unsignedBigInteger('changed_by_id')->nullable();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->string('reason', 1000)->nullable();
            $table->timestamp('created_at')->useCurrent();
            /*
             * Base schema treats status history as append-only audit data.
             * deleted_at is added only because this project explicitly requires
             * SoftDeletes on every Lead-domain model.
             */
            $table->softDeletes();
            $table->index(['lead_id', 'created_at'], 'lead_status_histories_lead_created_idx');
            $table->index(['changed_by_type', 'changed_by_id'], 'lead_status_histories_actor_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_status_histories');
    }
};
