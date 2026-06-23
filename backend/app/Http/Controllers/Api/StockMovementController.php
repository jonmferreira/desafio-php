<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMovementRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMovementController extends Controller
{
    public function index(Request $request, Product $product): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        return response()->json(
            $product->stockMovements()
                ->with('user:id,name')
                ->latest()
                ->paginate($perPage)
        );
    }

    public function store(StoreMovementRequest $request, Product $product): JsonResponse
    {
        $validated = $request->validated();

        $movement = DB::transaction(function () use ($product, $validated, $request) {
            // Trava a linha do produto: serializa requisicoes concorrentes para o
            // mesmo produto e evita race condition no calculo de saldo (API6).
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);

            if ($validated['type'] === 'out') {
                $balance = $locked->stockMovements()
                    ->selectRaw("COALESCE(SUM(CASE WHEN type = 'in' THEN quantity ELSE -quantity END), 0) as balance")
                    ->value('balance');

                if ($validated['quantity'] > $balance) {
                    throw ValidationException::withMessages([
                        'quantity' => ["Quantidade solicitada excede o saldo em estoque ({$balance})."],
                    ]);
                }
            }

            return $locked->stockMovements()->create([
                ...$validated,
                'user_id' => $request->user()->id,
            ]);
        });

        return response()->json($movement->load('user:id,name'), 201);
    }
}
