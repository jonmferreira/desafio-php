<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoMovementsSeeder extends Seeder
{
    public function run(): void
    {
        // Guard: já existe massa de dados de demo (mais de 7 movimentos do seed inicial)
        if (DB::table('stock_movements')->count() > 7) {
            return;
        }

        $adminId = DB::table('users')->where('email', 'admin@systock.com.br')->value('id');
        $operatorId = DB::table('users')->where('email', 'operador@systock.com.br')->value('id');

        if (! $adminId || ! $operatorId) {
            return;
        }

        $productIds = DB::table('products')->pluck('id')->toArray();

        if (empty($productIds)) {
            return;
        }

        $rows = [];
        $base = now();

        $inReasons = ['Reposição de estoque', 'Compra fornecedor', 'Devolução cliente', 'Transferência entrada'];
        $outReasons = ['Venda balcão', 'Venda delivery', 'Perda/vencimento', 'Transferência saída'];

        foreach ($productIds as $productId) {
            for ($day = 1; $day <= 30; $day++) {
                $date = $base->copy()->subDays($day);

                $inCount = rand(1, 3);
                for ($k = 0; $k < $inCount; $k++) {
                    $ts = $date->copy()->setTime(rand(7, 12), rand(0, 59), 0)->toDateTimeString();
                    $rows[] = [
                        'product_id' => $productId,
                        'user_id' => $adminId,
                        'type' => 'in',
                        'quantity' => rand(10, 80),
                        'reason' => $inReasons[array_rand($inReasons)],
                        'created_at' => $ts,
                        'updated_at' => $ts,
                    ];
                }

                $outCount = rand(1, 2);
                for ($k = 0; $k < $outCount; $k++) {
                    $ts = $date->copy()->setTime(rand(13, 20), rand(0, 59), 0)->toDateTimeString();
                    $rows[] = [
                        'product_id' => $productId,
                        'user_id' => $operatorId,
                        'type' => 'out',
                        'quantity' => rand(3, 30),
                        'reason' => $outReasons[array_rand($outReasons)],
                        'created_at' => $ts,
                        'updated_at' => $ts,
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('stock_movements')->insert($chunk);
        }
    }
}
