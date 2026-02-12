<?php

namespace Database\Seeders;

use App\Models\RankLogo;
use Illuminate\Database\Seeder;

class RankLogosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ranks = [
            // ERA I (Levels 1-50) - Green
            ['rank' => 1, 'name' => 'Recruit', 'era' => 1, 'min_level' => 1, 'max_level' => 10, 'color' => '#22c55e'],
            ['rank' => 2, 'name' => 'Private', 'era' => 1, 'min_level' => 11, 'max_level' => 20, 'color' => '#22c55e'],
            ['rank' => 3, 'name' => 'Private First Class', 'era' => 1, 'min_level' => 21, 'max_level' => 30, 'color' => '#22c55e'],
            ['rank' => 4, 'name' => 'Specialist', 'era' => 1, 'min_level' => 31, 'max_level' => 40, 'color' => '#22c55e'],
            ['rank' => 5, 'name' => 'Senior Specialist', 'era' => 1, 'min_level' => 41, 'max_level' => 50, 'color' => '#22c55e'],

            // ERA II (Levels 51-100) - Green
            ['rank' => 6, 'name' => 'Corporal', 'era' => 2, 'min_level' => 51, 'max_level' => 60, 'color' => '#22c55e'],
            ['rank' => 7, 'name' => 'Lance Corporal', 'era' => 2, 'min_level' => 61, 'max_level' => 70, 'color' => '#22c55e'],
            ['rank' => 8, 'name' => 'Sergeant', 'era' => 2, 'min_level' => 71, 'max_level' => 80, 'color' => '#22c55e'],
            ['rank' => 9, 'name' => 'Staff Sergeant', 'era' => 2, 'min_level' => 81, 'max_level' => 90, 'color' => '#22c55e'],
            ['rank' => 10, 'name' => 'Gunnery Sergeant', 'era' => 2, 'min_level' => 91, 'max_level' => 100, 'color' => '#22c55e'],

            // ERA III (Levels 101-150) - Blue
            ['rank' => 11, 'name' => 'Master Sergeant', 'era' => 3, 'min_level' => 101, 'max_level' => 110, 'color' => '#3b82f6'],
            ['rank' => 12, 'name' => 'First Sergeant', 'era' => 3, 'min_level' => 111, 'max_level' => 120, 'color' => '#3b82f6'],
            ['rank' => 13, 'name' => 'Sergeant Major', 'era' => 3, 'min_level' => 121, 'max_level' => 130, 'color' => '#3b82f6'],
            ['rank' => 14, 'name' => 'Command Sergeant Major', 'era' => 3, 'min_level' => 131, 'max_level' => 140, 'color' => '#3b82f6'],
            ['rank' => 15, 'name' => 'Sergeant Major of the Army', 'era' => 3, 'min_level' => 141, 'max_level' => 150, 'color' => '#3b82f6'],

            // ERA IV (Levels 151-200) - Blue
            ['rank' => 16, 'name' => 'Second Lieutenant', 'era' => 4, 'min_level' => 151, 'max_level' => 160, 'color' => '#3b82f6'],
            ['rank' => 17, 'name' => 'First Lieutenant', 'era' => 4, 'min_level' => 161, 'max_level' => 170, 'color' => '#3b82f6'],
            ['rank' => 18, 'name' => 'Captain', 'era' => 4, 'min_level' => 171, 'max_level' => 180, 'color' => '#3b82f6'],
            ['rank' => 19, 'name' => 'Major', 'era' => 4, 'min_level' => 181, 'max_level' => 190, 'color' => '#3b82f6'],
            ['rank' => 20, 'name' => 'Lieutenant Colonel', 'era' => 4, 'min_level' => 191, 'max_level' => 200, 'color' => '#3b82f6'],

            // ERA V (Levels 201-250) - Purple
            ['rank' => 21, 'name' => 'Colonel', 'era' => 5, 'min_level' => 201, 'max_level' => 210, 'color' => '#a855f7'],
            ['rank' => 22, 'name' => 'Brigadier General', 'era' => 5, 'min_level' => 211, 'max_level' => 220, 'color' => '#a855f7'],
            ['rank' => 23, 'name' => 'Major General', 'era' => 5, 'min_level' => 221, 'max_level' => 230, 'color' => '#a855f7'],
            ['rank' => 24, 'name' => 'Lieutenant General', 'era' => 5, 'min_level' => 231, 'max_level' => 240, 'color' => '#a855f7'],
            ['rank' => 25, 'name' => 'General', 'era' => 5, 'min_level' => 241, 'max_level' => 250, 'color' => '#a855f7'],

            // ERA VI (Levels 251-300) - Purple
            ['rank' => 26, 'name' => 'Marshal', 'era' => 6, 'min_level' => 251, 'max_level' => 260, 'color' => '#a855f7'],
            ['rank' => 27, 'name' => 'Field Marshal', 'era' => 6, 'min_level' => 261, 'max_level' => 270, 'color' => '#a855f7'],
            ['rank' => 28, 'name' => 'Grand Marshal', 'era' => 6, 'min_level' => 271, 'max_level' => 280, 'color' => '#a855f7'],
            ['rank' => 29, 'name' => 'Supreme Marshal', 'era' => 6, 'min_level' => 281, 'max_level' => 290, 'color' => '#a855f7'],
            ['rank' => 30, 'name' => 'Imperial Marshal', 'era' => 6, 'min_level' => 291, 'max_level' => 300, 'color' => '#a855f7'],

            // ERA VII (Levels 301-350) - Orange
            ['rank' => 31, 'name' => 'War Commander', 'era' => 7, 'min_level' => 301, 'max_level' => 310, 'color' => '#f97316'],
            ['rank' => 32, 'name' => 'High Commander', 'era' => 7, 'min_level' => 311, 'max_level' => 320, 'color' => '#f97316'],
            ['rank' => 33, 'name' => 'Supreme Commander', 'era' => 7, 'min_level' => 321, 'max_level' => 330, 'color' => '#f97316'],
            ['rank' => 34, 'name' => 'Grand Commander', 'era' => 7, 'min_level' => 331, 'max_level' => 340, 'color' => '#f97316'],
            ['rank' => 35, 'name' => 'Elite Commander', 'era' => 7, 'min_level' => 341, 'max_level' => 350, 'color' => '#f97316'],

            // ERA VIII (Levels 351-400) - Orange
            ['rank' => 36, 'name' => 'War Chief', 'era' => 8, 'min_level' => 351, 'max_level' => 360, 'color' => '#f97316'],
            ['rank' => 37, 'name' => 'Grand Chief', 'era' => 8, 'min_level' => 361, 'max_level' => 370, 'color' => '#f97316'],
            ['rank' => 38, 'name' => 'Supreme Chief', 'era' => 8, 'min_level' => 371, 'max_level' => 380, 'color' => '#f97316'],
            ['rank' => 39, 'name' => 'Elite Chief', 'era' => 8, 'min_level' => 381, 'max_level' => 390, 'color' => '#f97316'],
            ['rank' => 40, 'name' => 'Legendary Chief', 'era' => 8, 'min_level' => 391, 'max_level' => 400, 'color' => '#f97316'],

            // ERA IX (Levels 401-450) - Red
            ['rank' => 41, 'name' => 'War Master', 'era' => 9, 'min_level' => 401, 'max_level' => 410, 'color' => '#ef4444'],
            ['rank' => 42, 'name' => 'Grand Master', 'era' => 9, 'min_level' => 411, 'max_level' => 420, 'color' => '#ef4444'],
            ['rank' => 43, 'name' => 'Supreme Master', 'era' => 9, 'min_level' => 421, 'max_level' => 430, 'color' => '#ef4444'],
            ['rank' => 44, 'name' => 'Elite Master', 'era' => 9, 'min_level' => 431, 'max_level' => 440, 'color' => '#ef4444'],
            ['rank' => 45, 'name' => 'Legendary Master', 'era' => 9, 'min_level' => 441, 'max_level' => 450, 'color' => '#ef4444'],

            // ERA X (Levels 451-500) - Red with Gold accent
            ['rank' => 46, 'name' => 'War Sovereign', 'era' => 10, 'min_level' => 451, 'max_level' => 460, 'color' => '#ef4444'],
            ['rank' => 47, 'name' => 'Grand Sovereign', 'era' => 10, 'min_level' => 461, 'max_level' => 470, 'color' => '#ef4444'],
            ['rank' => 48, 'name' => 'Supreme Sovereign', 'era' => 10, 'min_level' => 471, 'max_level' => 480, 'color' => '#ef4444'],
            ['rank' => 49, 'name' => 'Elite Sovereign', 'era' => 10, 'min_level' => 481, 'max_level' => 490, 'color' => '#ef4444'],
            ['rank' => 50, 'name' => 'Eternal Warlord', 'era' => 10, 'min_level' => 491, 'max_level' => 500, 'color' => '#fbbf24'], // Gold for final rank
        ];

        foreach ($ranks as $index => $rank) {
            RankLogo::create([
                'rank' => $rank['rank'],
                'name' => $rank['name'],
                'era' => $rank['era'],
                'min_level' => $rank['min_level'],
                'max_level' => $rank['max_level'],
                'color' => $rank['color'],
                'sort_order' => $index,
                'logo_path' => null, // Admin will upload these later
            ]);
        }
    }
}
