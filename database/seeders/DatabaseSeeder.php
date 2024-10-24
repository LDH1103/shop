<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductModel;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 상품정보 팩토리
        ProductModel::factory()->count(5000)->create();

        $this->call([
            // 카테고리 시더
            // CategorySeeder::class,
        ]);
    }
}
