<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_addons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 190);
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('amount_max', 12, 2)->nullable();
            $table->string('price_type', 30)->default('fixed');
            $table->string('unit', 80)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('price_type');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_addons');
    }
};
