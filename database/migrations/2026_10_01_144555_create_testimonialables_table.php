<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonialables',function(Blueprint $table){
            $table->id();
            $table->foreignId('testimonial_id')->constrained('testimonials')->cascadeOnDelete();
            $table->morphs('testimonialable');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['testimonial_id','testimonialable_type','testimonialable_id'],'testimonialable_unique');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('testimonialables');
    }
};