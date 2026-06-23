<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $bebidas = Category::query()->where('name', 'Bebidas')->firstOrFail();
        $limpeza = Category::query()->where('name', 'Limpeza')->firstOrFail();
        $alimentos = Category::query()->where('name', 'Alimentos')->firstOrFail();
        $higiene = Category::query()->where('name', 'Higiene')->firstOrFail();

        $products = [
            [
                'category_id' => $bebidas->id,
                'sku' => 'SKU-0001',
                'name' => 'Refrigerante Cola 2L',
                'description' => 'Refrigerante sabor cola, garrafa de 2 litros',
                'unit' => 'un',
                'min_quantity' => 20,
                'price' => 8.50,
            ],
            [
                'category_id' => $bebidas->id,
                'sku' => 'SKU-0002',
                'name' => 'Agua Mineral 500ml',
                'description' => 'Agua mineral sem gas, garrafa de 500ml',
                'unit' => 'un',
                'min_quantity' => 50,
                'price' => 2.00,
            ],
            [
                'category_id' => $limpeza->id,
                'sku' => 'SKU-0003',
                'name' => 'Detergente Neutro 500ml',
                'description' => 'Detergente liquido neutro para louças',
                'unit' => 'un',
                'min_quantity' => 15,
                'price' => 3.20,
            ],
            [
                'category_id' => $alimentos->id,
                'sku' => 'SKU-0004',
                'name' => 'Arroz Branco 5kg',
                'description' => 'Arroz branco tipo 1, pacote de 5kg',
                'unit' => 'kg',
                'min_quantity' => 10,
                'price' => 24.90,
            ],
            [
                'category_id' => $higiene->id,
                'sku' => 'SKU-0005',
                'name' => 'Sabonete em Barra 90g',
                'description' => 'Sabonete em barra, unidade de 90g',
                'unit' => 'un',
                'min_quantity' => 30,
                'price' => 1.80,
            ],
        ];

        foreach ($products as $product) {
            Product::query()->firstOrCreate(['sku' => $product['sku']], $product);
        }
    }
}
