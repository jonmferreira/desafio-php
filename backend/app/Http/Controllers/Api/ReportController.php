<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function stockFlow(Request $request): JsonResponse
    {
        $days = min((int) $request->query('days', 30), 90);

        $rows = DB::table('stock_movements')
            ->selectRaw('DATE(created_at) as date, SUM(quantity) as total')
            ->where('created_at', '>=', now()->subDays($days)->startOfDay())
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'total' => (int) $row->total,
            ]);

        return response()->json($rows);
    }

    public function ruptura(): JsonResponse
    {
        $rows = DB::select('
            SELECT id, sku, name, min_fardos, unit, category, total_fardos
            FROM (
                SELECT
                    p.id, p.sku, p.name, p.min_fardos, p.unit,
                    c.name AS category,
                    COALESCE(SUM(li.quantidade_fardos), 0) AS total_fardos
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN lote_items li ON li.product_id = p.id
                GROUP BY p.id, p.sku, p.name, p.min_fardos, p.unit, c.name
            ) sub
            WHERE total_fardos < min_fardos
            ORDER BY total_fardos ASC
        ');

        return response()->json(array_map(fn ($r) => [
            'id' => $r->id,
            'sku' => $r->sku,
            'name' => $r->name,
            'min_fardos' => (int) $r->min_fardos,
            'unit' => $r->unit,
            'category' => $r->category,
            'total_fardos' => (int) $r->total_fardos,
        ], $rows));
    }

    public function capitalPorCliente(): JsonResponse
    {
        $rows = DB::select('
            SELECT
                u.id,
                u.name,
                COALESCE(SUM(li.valor_unitario * li.itens_por_fardo * li.quantidade_fardos), 0) AS capital
            FROM users u
            LEFT JOIN lotes l ON l.user_id = u.id
            LEFT JOIN lote_items li ON li.lote_id = l.id
            GROUP BY u.id, u.name
            ORDER BY capital DESC
        ');

        return response()->json(array_map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'capital' => round((float) $r->capital, 2),
        ], $rows));
    }

    public function estoquePorProduto(): JsonResponse
    {
        $rows = DB::select('
            SELECT
                p.id,
                p.sku,
                p.name,
                p.unit,
                p.min_fardos,
                COALESCE(SUM(li.quantidade_fardos), 0) AS total_fardos
            FROM products p
            LEFT JOIN lote_items li ON li.product_id = p.id
            GROUP BY p.id, p.sku, p.name, p.unit, p.min_fardos
            ORDER BY total_fardos DESC
        ');

        return response()->json(array_map(fn ($r) => [
            'id' => $r->id,
            'sku' => $r->sku,
            'name' => $r->name,
            'unit' => $r->unit,
            'min_fardos' => (int) $r->min_fardos,
            'total_fardos' => (int) $r->total_fardos,
        ], $rows));
    }

    public function valorEstoque(): JsonResponse
    {
        $rows = DB::select('
            SELECT
                c.id,
                c.name AS category,
                COUNT(DISTINCT p.id) AS product_count,
                COALESCE(SUM(
                    CASE WHEN COALESCE(fardos.total_fardos, 0) > 0
                         THEN CAST(p.price AS REAL) * COALESCE(fardos.total_fardos, 0)
                         ELSE 0
                    END
                ), 0) AS total_value
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            LEFT JOIN (
                SELECT product_id, SUM(quantidade_fardos) AS total_fardos
                FROM lote_items
                GROUP BY product_id
            ) fardos ON fardos.product_id = p.id
            GROUP BY c.id, c.name
            ORDER BY total_value DESC
        ');

        return response()->json(array_map(fn ($r) => [
            'id' => $r->id,
            'category' => $r->category,
            'product_count' => (int) $r->product_count,
            'total_value' => (float) $r->total_value,
        ], $rows));
    }

    public function giro(): JsonResponse
    {
        $cutoff = now()->subDays(30)->startOfDay()->toDateTimeString();

        $rows = DB::select('
            SELECT
                p.id, p.sku, p.name,
                COUNT(sm.id) AS operation_count,
                SUM(sm.quantity) AS total_quantity
            FROM products p
            JOIN stock_movements sm ON sm.product_id = p.id
            WHERE sm.created_at >= ?
            GROUP BY p.id, p.sku, p.name
            ORDER BY total_quantity DESC
            LIMIT 10
        ', [$cutoff]);

        return response()->json(array_map(fn ($r) => [
            'id' => $r->id,
            'sku' => $r->sku,
            'name' => $r->name,
            'operation_count' => (int) $r->operation_count,
            'total_quantity' => (int) $r->total_quantity,
        ], $rows));
    }
}
