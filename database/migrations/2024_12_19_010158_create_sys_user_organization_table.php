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
        Schema::create('sys_user_organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('sys_users')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained('sys_organizations')->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestampsTz();

            $table->unique(['user_id', 'organization_id']);
        });

        Schema::create('sys_user_organization_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_organization_id')->constrained('sys_user_organizations')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('sys_roles')->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestampsTz();

            $table->unique(['user_organization_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_user_organization_roles');
        Schema::dropIfExists('sys_user_organizations');
    }
};
