<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_package_id')->constrained('pricing_packages')->restrictOnDelete();
            $table->string('label', 190);
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('amount_max', 12, 2)->nullable();
            $table->string('price_type', 30)->default('fixed');
            $table->string('unit', 80)->nullable();
            $table->string('prefix', 40)->nullable();
            $table->string('suffix', 40)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pricing_package_id', 'is_active', 'sort_order'], 'pricing_items_package_scope_idx');
            $table->index(['price_type', 'is_active']);
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_items');
    }
};
