<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_events', function (Blueprint $table) {
            $table->id();
            $table->integer('server_id')->nullable()->index();
            $table->string('event_type', 100)->index();
            $table->json('payload');
            $table->timestamp('event_timestamp')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_events');
    }
};
