<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_form_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('lead_form_field_id')->nullable()->constrained('lead_form_fields')->nullOnDelete();
            $table->string('field_key', 100);
            $table->string('field_label', 150);
            $table->text('answer');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['lead_id', 'field_key'], 'lead_form_answers_lead_key_unique');
            $table->index(['lead_id', 'sort_order', 'id'], 'lead_form_answers_lead_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_form_answers');
    }
};
