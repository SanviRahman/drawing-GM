<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_settings', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->foreignId('default_campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->string('fallback_mode', 30)->default('homepage');
            $table->foreignId('fallback_page_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('campaign_settings')->insert([
            'id' => 1,
            'default_campaign_id' => null,
            'fallback_mode' => 'homepage',
            'fallback_page_id' => null,
            'updated_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_settings');
    }
};
