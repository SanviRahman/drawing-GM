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
            $table->string('group_name', 80)->index();
            $table->string('setting_key', 150)->unique();
            $table->longText('setting_value')->nullable();
            $table->string('value_type', 30)->default('string');
            $table->boolean('is_public')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
