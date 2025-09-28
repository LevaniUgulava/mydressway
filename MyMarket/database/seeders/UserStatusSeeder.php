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
            "time" => 2,
            "expansion" => "month",
            "limit" => 500
        ]);
    }
}
