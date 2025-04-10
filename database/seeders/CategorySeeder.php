<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::insert([
            ['name' => 'Electronics', 'description' => 'Devices and gadgets'],
            ['name' => 'Clothing', 'description' => 'Men, women, and kids clothing'],
            ['name' => 'Home Appliances', 'description' => 'Appliances for daily use'],
            ['name' => 'Books', 'description' => 'Novels, comics, and educational books'],
            ['name' => 'Toys', 'description' => 'Toys for kids and adults'],
        ]);
    }
}