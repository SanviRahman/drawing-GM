<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_services', function (Blueprint $table): void {
            /*
             * `id`, timestamps and deleted_at are deliberate operational
             * extensions because this project requires recoverable Admin CRUD.
             * The documented business uniqueness remains (lead_id, service_id).
             */
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['lead_id', 'service_id'], 'lead_services_lead_service_unique');
            $table->index(['lead_id', 'deleted_at'], 'lead_services_lead_deleted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_services');
    }
};
