<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ApiUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'api@armabattles.se'],
            [
                'name' => 'API Service',
                'password' => Hash::make(bin2hex(random_bytes(32))),
                'role' => 'admin',
            ]
        );

        $user->tokens()->delete();

        $token = $user->createToken('game-server-api', ['*']);

        $this->command->info('');
        $this->command->info('=================================================');
        $this->command->info('API User created successfully!');
        $this->command->info('=================================================');
        $this->command->info('');
        $this->command->info('User Email: api@armabattles.se');
        $this->command->info('');
        $this->command->warn('YOUR API TOKEN (save this, it will not be shown again):');
        $this->command->info('');
        $this->command->info($token->plainTextToken);
        $this->command->info('');
        $this->command->info('=================================================');
        $this->command->info('');
        $this->command->info('Use this token in your requests:');
        $this->command->info('Authorization: Bearer ' . $token->plainTextToken);
        $this->command->info('');
    }
}
