<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_kills', function (Blueprint $table) {
            $table->id();
            $table->integer('server_id')->index();
            $table->string('player_uuid', 255)->index();
            $table->integer('kills_total')->default(0);
            $table->integer('kills_delta')->default(0);
            $table->string('kill_type', 50)->default('unknown'); // ai, player, etc.
            $table->timestamp('timestamp')->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['player_uuid', 'server_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_kills');
    }
};
