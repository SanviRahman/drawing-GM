<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_form_fields', function (Blueprint $table): void {
            $table->id();
            $table->string('label', 150);
            $table->string('field_key', 100)->unique();
            $table->string('placeholder', 190)->nullable();
            $table->json('options');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order', 'id'], 'lead_form_fields_active_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_form_fields');
    }
};
