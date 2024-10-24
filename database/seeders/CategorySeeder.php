<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => '의류/잡화'],
            ['name' => '뷰티'],
            ['name' => '생활용품'],
            ['name' => '식품'],
            ['name' => '건강식품'],
            ['name' => '디지털'],
            ['name' => '반려동물'],
        ]);
    }
}
