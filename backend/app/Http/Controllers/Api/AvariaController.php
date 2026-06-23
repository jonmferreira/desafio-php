<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvariaRequest;
use App\Models\Avaria;
use App\Models\Lote;
use Illuminate\Http\JsonResponse;

class AvariaController extends Controller
{
    public function store(StoreAvariaRequest $request, Lote $lote): JsonResponse
    {
        $data = $request->validated();
        $data['lote_id'] = $lote->id;

        $avaria = Avaria::query()->create($data);

        return response()->json($avaria, 201);
    }

    public function destroy(Lote $lote, Avaria $avaria): JsonResponse
    {
        abort_if($avaria->lote_id !== $lote->id, 404);

        $avaria->delete();

        return response()->json(null, 204);
    }
}
