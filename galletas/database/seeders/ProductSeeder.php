<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $flavors = [
            [
                'name'       => 'Nutella',
                'color_hex'  => '#7B3F00',   // Chocolate
                'image_path' => 'images/galletas/nutella.jpg',
                'stock'      => 20,
            ],
            [
                'name'       => 'Red Velvet',
                'color_hex'  => '#DC143C',   // Carmesí
                'image_path' => 'images/galletas/red-velvet.jpg',
                'stock'      => 20,
            ],
            [
                'name'       => 'Leche Klim',
                'color_hex'  => '#F5F5DC',   // Crema
                'image_path' => 'images/galletas/leche-klim.jpg',
                'stock'      => 20,
            ],
            [
                'name'       => 'Pie de Limón',
                'color_hex'  => '#FDE047',   // Amarillo limón
                'image_path' => 'images/galletas/pie-de-limon.jpg',
                'stock'      => 20,
            ],
            [
                'name'       => 'Nucita',
                'color_hex'  => '#E2725B',   // Terracota
                'image_path' => 'images/galletas/nucita.jpg',
                'stock'      => 20,
            ],
        ];

        foreach ($flavors as $flavor) {
            $product = Product::create([
                'name'       => $flavor['name'],
                'slug'       => Str::slug($flavor['name']),
                'price'      => 10000,
                'image_path' => $flavor['image_path'],
                'color_hex'  => $flavor['color_hex'],
                'is_active'  => true,
            ]);

            Stock::create([
                'product_id' => $product->id,
                'quantity'   => $flavor['stock'],
            ]);
        }
    }
}