<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_package_addon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_package_id')->constrained('pricing_packages')->restrictOnDelete();
            $table->foreignId('pricing_addon_id')->constrained('pricing_addons')->restrictOnDelete();
            $table->json('override_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['pricing_package_id', 'pricing_addon_id'],
                'pricing_package_addon_unique'
);
            $table->index(
                ['pricing_package_id', 'deleted_at'],
                'pricing_package_addon_package_idx'
            );
            $table->index(
                ['pricing_addon_id', 'deleted_at'],
                'pricing_package_addon_addon_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_package_addon');
    }
};
