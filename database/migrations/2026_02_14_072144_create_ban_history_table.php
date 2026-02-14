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
        Schema::create('ban_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // banned, unbanned, temp_ban_expired
            $table->text('reason')->nullable();
            $table->string('ban_type')->nullable(); // permanent, temporary, hardware, ip_range
            $table->timestamp('banned_until')->nullable();
            $table->string('hardware_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->foreignId('actioned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ban_history');
    }
};
