<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoteItemRequest;
use App\Models\Lote;
use App\Models\LoteItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoteItemController extends Controller
{
    public function store(StoreLoteItemRequest $request, Lote $lote): JsonResponse
    {
        $data = $request->validated();
        $data['lote_id'] = $lote->id;

        $item = LoteItem::updateOrCreate(
            ['lote_id' => $lote->id, 'product_id' => $data['product_id']],
            $data
        );

        return response()->json($item->load('product:id,sku,name,unit,peso'), 201);
    }

    public function update(Request $request, Lote $lote, int $productId): JsonResponse
    {
        $validated = $request->validate([
            'quantidade_fardos' => ['sometimes', 'required', 'integer', 'min:1', 'max:1000000'],
            'itens_por_fardo' => ['sometimes', 'required', 'integer', 'min:1', 'max:10000'],
            'valor_unitario' => ['sometimes', 'required', 'numeric', 'min:0', 'max:999999999.99'],
        ]);

        $item = LoteItem::where('lote_id', $lote->id)
            ->where('product_id', $productId)
            ->firstOrFail();

        $item->update($validated);

        return response()->json($item->load('product:id,sku,name,unit,peso'));
    }

    public function destroy(Lote $lote, int $productId): JsonResponse
    {
        $deleted = LoteItem::where('lote_id', $lote->id)
            ->where('product_id', $productId)
            ->delete();

        if ($deleted === 0) {
            abort(404, 'Item não encontrado no lote.');
        }

        return response()->json(null, 204);
    }
}
