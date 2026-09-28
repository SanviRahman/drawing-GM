<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group_name', 80);
            $table->string('setting_key', 150)->unique();
            $table->longText('setting_value')->nullable();
            $table->string('value_type', 30)->default('string');
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index(['group_name', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
