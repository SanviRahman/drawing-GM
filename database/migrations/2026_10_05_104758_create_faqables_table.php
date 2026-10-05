<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqables', function (Blueprint $table): void {
            $table->foreignId('faq_id')->constrained('faqs')->cascadeOnDelete();
            $table->string('faqable_type');
            $table->unsignedBigInteger('faqable_id');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['faq_id', 'faqable_type', 'faqable_id'],'faqable_unique');
            $table->index(['faqable_type', 'faqable_id', 'sort_order'],'faqables_target_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqables');
    }
};
