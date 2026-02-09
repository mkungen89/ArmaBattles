<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('supply_deliveries')) {
            return;
        }

        Schema::create('supply_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id');
            $table->string('player_name');
            $table->string('player_uuid')->nullable()->index();
            $table->integer('player_id')->nullable();
            $table->string('player_faction')->nullable();
            $table->string('supply_type')->nullable();
            $table->integer('amount')->nullable();
            $table->json('position')->nullable();
            $table->integer('estimated_amount')->nullable();
            $table->integer('xp_awarded')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['server_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_deliveries');
    }
};
