<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_channels', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 30);
            $table->string('label', 120);
            $table->string('region', 120)->nullable();
            $table->string('value', 255);
            $table->string('display_value', 120)->nullable();
            $table->text('message_template')->nullable();
            $table->string('icon', 100);
            $table->string('colour', 20);
            $table->string('availability_text', 120)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('track_clicks')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active', 'sort_order'], 'contact_channels_type_active_order_idx');
            $table->index('is_default', 'contact_channels_default_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_channels');
    }
};
