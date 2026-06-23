<?php

namespace Database\Seeders;

use App\Models\Avaria;
use App\Models\Lote;
use App\Models\LoteItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoteSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('lotes')->count() > 0) {
            return;
        }

        $admin    = User::query()->where('email', 'admin@systock.com.br')->firstOrFail();
        $operator = User::query()->where('email', 'operador@systock.com.br')->firstOrFail();

        $products = Product::query()->get()->keyBy('sku');

        $lotes = [
            [
                'user'   => $admin,
                'frete'  => 120.00,
                'itens'  => [
                    ['sku' => 'SKU-0001', 'quantidade_fardos' => 10, 'itens_por_fardo' => 12, 'valor_unitario' => 8.00],
                    ['sku' => 'SKU-0002', 'quantidade_fardos' => 20, 'itens_por_fardo' => 24, 'valor_unitario' => 1.80],
                    ['sku' => 'SKU-0004', 'quantidade_fardos' => 5,  'itens_por_fardo' => 5,  'valor_unitario' => 22.00],
                ],
                'avarias' => [
                    ['descricao' => 'Embalagens amassadas — 2 fardos de refrigerante', 'valor' => 48.00],
                ],
            ],
            [
                'user'   => $admin,
                'frete'  => 80.00,
                'itens'  => [
                    ['sku' => 'SKU-0003', 'quantidade_fardos' => 8, 'itens_por_fardo' => 12, 'valor_unitario' => 2.90],
                    ['sku' => 'SKU-0005', 'quantidade_fardos' => 15, 'itens_por_fardo' => 48, 'valor_unitario' => 1.60],
                ],
                'avarias' => [],
            ],
            [
                'user'   => $operator,
                'frete'  => 60.00,
                'itens'  => [
                    ['sku' => 'SKU-0001', 'quantidade_fardos' => 6, 'itens_por_fardo' => 12, 'valor_unitario' => 8.20],
                    ['sku' => 'SKU-0004', 'quantidade_fardos' => 3, 'itens_por_fardo' => 5,  'valor_unitario' => 23.50],
                ],
                'avarias' => [
                    ['descricao' => 'Perda por umidade em 1 fardo de arroz', 'valor' => 35.00],
                ],
            ],
            [
                'user'   => $operator,
                'frete'  => 45.00,
                'itens'  => [
                    ['sku' => 'SKU-0002', 'quantidade_fardos' => 12, 'itens_por_fardo' => 24, 'valor_unitario' => 1.90],
                    ['sku' => 'SKU-0005', 'quantidade_fardos' => 10, 'itens_por_fardo' => 48, 'valor_unitario' => 1.65],
                ],
                'avarias' => [],
            ],
        ];

        foreach ($lotes as $def) {
            $lote = Lote::query()->create([
                'user_id' => $def['user']->id,
                'frete'   => $def['frete'],
            ]);

            foreach ($def['itens'] as $itemDef) {
                LoteItem::query()->create([
                    'lote_id'           => $lote->id,
                    'product_id'        => $products[$itemDef['sku']]->id,
                    'quantidade_fardos' => $itemDef['quantidade_fardos'],
                    'itens_por_fardo'   => $itemDef['itens_por_fardo'],
                    'valor_unitario'    => $itemDef['valor_unitario'],
                ]);
            }

            foreach ($def['avarias'] as $avariaDef) {
                Avaria::query()->create([
                    'lote_id'   => $lote->id,
                    'descricao' => $avariaDef['descricao'],
                    'valor'     => $avariaDef['valor'],
                ]);
            }
        }
    }
}
