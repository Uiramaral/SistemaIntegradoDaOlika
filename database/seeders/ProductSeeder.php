<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Pães Rústicos
            [
                'category_id' => 1,
                'name' => 'Pão de Fermentação Natural',
                'description' => 'Pão artesanal com fermentação natural de 24 horas. Crocante por fora, macio por dentro.',
                'price' => 18.00,
                'image_url' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800&auto=format&fit=crop',
                'is_featured' => false,
                'is_available' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => 1,
                'name' => 'Baguete Francesa',
                'description' => 'Baguete autêntica, crocante e dourada. Perfeita para acompanhamentos.',
                'price' => 12.00,
                'image_url' => 'https://images.unsplash.com/photo-1534620808146-d33bb39128b2?w=800&auto=format&fit=crop',
                'is_featured' => false,
                'is_available' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => 1,
                'name' => 'Ciabatta Italiana',
                'description' => 'Pão italiano com miolo aerado e casca fina. Ideal para sanduíches.',
                'price' => 16.00,
                'image_url' => 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=800&auto=format&fit=crop',
                'is_featured' => false,
                'is_available' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],

            // Focaccias
            [
                'category_id' => 2,
                'name' => 'Focaccia de Alecrim',
                'description' => 'Focaccia italiana tradicional com azeite extra virgem e alecrim fresco.',
                'price' => 22.00,
                'image_url' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=800&auto=format&fit=crop',
                'is_featured' => true,
                'is_available' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => 2,
                'name' => 'Focaccia de Tomate e Manjericão',
                'description' => 'Focaccia com tomates cereja e manjericão fresco.',
                'price' => 24.00,
                'image_url' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=800&auto=format&fit=crop',
                'is_featured' => false,
                'is_available' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],

            // Pães Doces
            [
                'category_id' => 3,
                'name' => 'Croissant de Manteiga',
                'description' => 'Croissant francês com camadas folhadas e manteiga de primeira qualidade.',
                'price' => 8.00,
                'image_url' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=800&auto=format&fit=crop',
                'is_featured' => false,
                'is_available' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => 3,
                'name' => 'Pão de Queijo',
                'description' => 'Tradicional pão de queijo mineiro, quentinho e sequinho.',
                'price' => 3.50,
                'image_url' => 'https://images.unsplash.com/photo-1618164436241-4473940d1f5c?w=800&auto=format&fit=crop',
                'is_featured' => true,
                'is_available' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],

            // Brownies
            [
                'category_id' => 4,
                'name' => 'Brownie de Chocolate',
                'description' => 'Brownie denso e úmido com chocolate belga 70% cacau.',
                'price' => 10.00,
                'image_url' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=800&auto=format&fit=crop',
                'is_featured' => false,
                'is_available' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => 4,
                'name' => 'Brownie de Nozes',
                'description' => 'Brownie com nozes crocantes e chocolate intenso.',
                'price' => 12.00,
                'image_url' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=800&auto=format&fit=crop',
                'is_featured' => false,
                'is_available' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],

            // Pães de Forma
            [
                'category_id' => 5,
                'name' => 'Pão de Forma Integral',
                'description' => 'Pão de forma integral com grãos e sementes. Rico em fibras e nutrientes.',
                'price' => 15.00,
                'image_url' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=800&auto=format&fit=crop',
                'is_featured' => false,
                'is_available' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
