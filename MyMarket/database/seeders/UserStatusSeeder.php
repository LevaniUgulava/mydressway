<?php

namespace Database\Seeders;

use App\Models\Userstatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Userstatus::create([
            "name" => "სტატუსის გარეშე",
            "toachieve" => 100,
            "start_data" => now(),
            "end_date" => now()->addDay(5)
        ]);
    }
}
