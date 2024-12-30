<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create users table migration.
 * 
 * This migration creates the users table with all necessary fields
 * for user authentication and profile management.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile', 10)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->string('profile_photo')->nullable();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for frequently queried columns
            $table->index('mobile');
            $table->index('email');
            $table->index(['deleted_at', 'mobile']);
            $table->index(['deleted_at', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
