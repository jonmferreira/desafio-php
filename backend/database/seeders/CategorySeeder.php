<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Bebidas', 'Limpeza', 'Alimentos', 'Higiene', 'Papelaria'] as $name) {
            Category::query()->firstOrCreate(['name' => $name]);
        }
    }
}
