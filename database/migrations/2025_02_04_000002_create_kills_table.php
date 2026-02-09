<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kills', function (Blueprint $table) {
            $table->id();
            $table->integer('server_id')->index();
            $table->foreignId('killer_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('victim_id')->nullable()->constrained('players')->nullOnDelete();
            $table->string('killer_name', 255);
            $table->string('victim_name', 255);
            $table->string('weapon', 255);
            $table->timestamp('timestamp')->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index('killer_id');
            $table->index('victim_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kills');
    }
};
