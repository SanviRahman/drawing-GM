<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_event_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tracking_provider_id')->constrained('tracking_providers')->restrictOnDelete();
            $table->string('internal_event', 100);
            $table->string('provider_event', 120);
            $table->json('parameter_map')->nullable();
            $table->boolean('requires_marketing_consent')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tracking_provider_id', 'internal_event'],'tracking_event_provider_internal_unique');
            $table->index(['is_enabled', 'internal_event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_event_rules');
    }
};
