<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    public function run(): void
    {
        if (StockMovement::query()->exists()) {
            return;
        }

        $admin = User::query()->where('email', 'admin@systock.com.br')->firstOrFail();
        $operator = User::query()->where('email', 'operador@systock.com.br')->firstOrFail();

        $initialQuantities = [
            'SKU-0001' => 50,
            'SKU-0002' => 100,
            'SKU-0003' => 30,
            'SKU-0004' => 25,
            'SKU-0005' => 60,
        ];

        foreach ($initialQuantities as $sku => $quantity) {
            $product = Product::query()->where('sku', $sku)->firstOrFail();

            $product->stockMovements()->create([
                'user_id' => $admin->id,
                'type' => 'in',
                'quantity' => $quantity,
                'reason' => 'Estoque inicial',
            ]);
        }

        // Algumas saidas de exemplo para demonstrar o calculo de saldo.
        $refrigerante = Product::query()->where('sku', 'SKU-0001')->firstOrFail();
        $refrigerante->stockMovements()->create([
            'user_id' => $operator->id,
            'type' => 'out',
            'quantity' => 5,
            'reason' => 'Venda balcao',
        ]);

        $arroz = Product::query()->where('sku', 'SKU-0004')->firstOrFail();
        $arroz->stockMovements()->create([
            'user_id' => $operator->id,
            'type' => 'out',
            'quantity' => 18,
            'reason' => 'Venda balcao',
        ]);
    }
}
