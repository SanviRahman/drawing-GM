<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials',function(Blueprint $table){
            $table->id();
            $table->string('type',30)->default('text');
            $table->string('customer_name',150);
            $table->string('customer_title',150)->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('review')->nullable();
            $table->string('source',50)->default('direct');
            $table->string('source_url',255)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};