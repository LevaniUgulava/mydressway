<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Maincategory;
use App\Models\Subcategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Maincategory::create([
            "ka_name" => "ქალი",
            "en_name" => "Woman"

        ]);
        Category::create([
            "ka_name" => "ტანსაცმელი",
            "en_name" => "Clothes"
        ]);
        Subcategory::create([
            "ka_name" => "კაბა",
            "en_name" => "Dress"
        ]);
    }
}
