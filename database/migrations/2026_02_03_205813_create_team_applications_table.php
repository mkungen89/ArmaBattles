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
        Schema::create('team_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'user_id', 'status'], 'unique_pending_application');
        });

        // Add recruitment columns to teams table
        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('is_recruiting')->default(true)->after('is_verified');
            $table->text('recruitment_message')->nullable()->after('is_recruiting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_applications');

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['is_recruiting', 'recruitment_message']);
        });
    }
};
