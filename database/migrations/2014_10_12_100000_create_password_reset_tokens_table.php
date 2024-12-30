<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create password reset tokens table migration.
 * 
 * This migration creates the password_reset_tokens table which stores
 * tokens for password reset functionality. The table supports both
 * email and mobile-based password resets.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            // Can be either email or mobile
            $table->string('identifier')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Add index for quick lookups
            $table->index(['identifier', 'token']);
            $table->index(['identifier', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
