<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'name' => ucfirst(fake()->words(3, true)),
            'description' => fake()->sentence(),
            'unit' => fake()->randomElement(['un', 'cx', 'kg', 'l']),
            'peso'       => fake()->randomFloat(3, 0.5, 20),
            'min_fardos' => fake()->numberBetween(3, 20),
            'price'      => fake()->randomFloat(2, 1, 500),
        ];
    }
}
