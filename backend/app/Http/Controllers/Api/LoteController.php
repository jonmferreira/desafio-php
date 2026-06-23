<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoteRequest;
use App\Models\Lote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        $query = Lote::query()
            ->with('user:id,name')
            ->when($request->query('user_id'), fn ($q, $v) => $q->where('user_id', $v))
            ->orderByDesc('created_at');

        return response()->json($query->paginate($perPage));
    }

    public function store(StoreLoteRequest $request): JsonResponse
    {
        $lote = Lote::query()->create($request->validated());

        return response()->json($lote->load('user:id,name'), 201);
    }

    public function show(Lote $lote): JsonResponse
    {
        $lote->load(['user:id,name', 'itens.product:id,sku,name,unit,peso', 'avarias', 'saidas.product:id,sku,name', 'saidas.user:id,name']);

        $totalItens = $lote->itens->sum(fn ($item) => $item->valor_unitario * $item->itens_por_fardo * $item->quantidade_fardos);
        $totalAvarias = $lote->avarias->sum('valor');
        $pesoTotal = $lote->itens->sum(fn ($item) => ($item->product->peso ?? 0) * $item->itens_por_fardo * $item->quantidade_fardos);

        return response()->json([
            'lote'          => $lote,
            'total_itens'   => round((float) $totalItens, 2),
            'total_avarias' => round((float) $totalAvarias, 2),
            'total'         => round((float) ($totalItens + (float) $lote->frete + $totalAvarias), 2),
            'peso_total'    => round((float) $pesoTotal, 3),
        ]);
    }

    public function destroy(Lote $lote): JsonResponse
    {
        $lote->delete();

        return response()->json(null, 204);
    }

    public function volume(Lote $lote): JsonResponse
    {
        $lote->load('itens.product:id,peso');

        $pesoTotal = $lote->itens->sum(
            fn ($item) => ($item->product->peso ?? 0) * $item->itens_por_fardo * $item->quantidade_fardos
        );

        $totalFardos = $lote->itens->sum('quantidade_fardos');

        return response()->json([
            'lote_id'      => $lote->id,
            'peso_total'   => round((float) $pesoTotal, 3),
            'total_fardos' => (int) $totalFardos,
        ]);
    }
}
