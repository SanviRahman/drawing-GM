<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('location', 80)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->nullOnDelete();
            $table->string('label', 150);
            $table->string('link_type', 30)->default('url');
            $table->string('linkable_type')->nullable();
            $table->unsignedBigInteger('linkable_id')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('target', 10)->default('_self');
            $table->string('css_class', 150)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('faq_section_enabled')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['menu_id', 'parent_id', 'sort_order']);
            $table->index(['linkable_type', 'linkable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
    }
};
