<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE achievement_progress ALTER COLUMN current_value TYPE numeric(12,2) USING current_value::numeric(12,2)');
        DB::statement('ALTER TABLE achievement_progress ALTER COLUMN target_value TYPE numeric(12,2) USING target_value::numeric(12,2)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE achievement_progress ALTER COLUMN current_value TYPE integer USING current_value::integer');
        DB::statement('ALTER TABLE achievement_progress ALTER COLUMN target_value TYPE integer USING target_value::integer');
    }
};
