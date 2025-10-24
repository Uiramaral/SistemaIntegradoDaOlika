<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Pães Rústicos',
                'description' => 'Pães artesanais com fermentação natural',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Focaccias',
                'description' => 'Focaccias italianas tradicionais',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Pães Doces',
                'description' => 'Pães doces e folhados',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Brownies',
                'description' => 'Brownies artesanais',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Pães de Forma',
                'description' => 'Pães de forma integrais',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Novidades',
                'description' => 'Novos produtos da padaria',
                'is_active' => true,
                'sort_order' => 0,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
