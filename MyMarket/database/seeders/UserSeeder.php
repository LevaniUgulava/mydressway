<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'admin',
            'surname' => 'ugulava',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'privacy_policy_agreed' => true
        ]);

        $user->assignRole('admin');
    }
}
