<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaidaRequest;
use App\Models\LoteItem;
use App\Models\Saida;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaidaController extends Controller
{
    public function store(StoreSaidaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        return DB::transaction(function () use ($data): JsonResponse {
            $item = LoteItem::where('lote_id', $data['lote_id'])
                ->where('product_id', $data['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($item->quantidade_fardos < $data['quantidade_fardos']) {
                abort(422, 'Quantidade de fardos insuficiente no lote.');
            }

            $item->decrement('quantidade_fardos', $data['quantidade_fardos']);

            $saida = Saida::query()->create($data);

            return response()->json(
                $saida->load(['product:id,sku,name', 'user:id,name', 'lote:id,numero,user_id']),
                201
            );
        });
    }
}
