<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sys_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('sys_menus')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('icon')->nullable();
            $table->string('show_if_has_permission')->nullable();
            $table->string('url')->nullable();
            $table->integer('order')->nullable()->default(1);
            $table->timestampsTz();

            $table->unique(['parent_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_menus');
    }
};
