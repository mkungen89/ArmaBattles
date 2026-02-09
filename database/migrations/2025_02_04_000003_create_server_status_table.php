<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_status', function (Blueprint $table) {
            $table->id();
            $table->integer('server_id')->index();
            $table->string('server_name', 255);
            $table->string('map', 255);
            $table->integer('players');
            $table->integer('max_players');
            $table->integer('ping');
            $table->timestamp('timestamp')->index();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_status');
    }
};
