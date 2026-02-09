<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_sessions', function (Blueprint $table) {
            $table->id();
            $table->integer('server_id')->index();
            $table->string('player_name', 255)->index();
            $table->string('player_uuid', 255)->nullable()->index();
            $table->enum('event_type', ['connect', 'disconnect'])->index();
            $table->timestamp('timestamp')->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['player_name', 'server_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_sessions');
    }
};
