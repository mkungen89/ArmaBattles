<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Set user with steam_id 76561199176944069 as admin
        DB::table('users')
            ->where('steam_id', '76561199176944069')
            ->update(['role' => 'admin']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('steam_id', '76561199176944069')
            ->update(['role' => 'user']);
    }
};
