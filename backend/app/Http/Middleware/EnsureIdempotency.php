<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotency
{
    /**
     * Garante que requisicoes repetidas com a mesma Idempotency-Key nao dupliquem o efeito.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key');

        if (! $key) {
            return new JsonResponse([
                'message' => 'O header Idempotency-Key é obrigatório nesta rota.',
            ], 400);
        }

        $userId = $request->user()->id;
        $method = $request->method();
        $path = $request->path();

        $existing = IdempotencyKey::query()
            ->where('user_id', $userId)
            ->where('key', $key)
            ->where('method', $method)
            ->where('path', $path)
            ->first();

        if ($existing) {
            return new JsonResponse($existing->response_body, $existing->response_status, [
                'X-Idempotent-Replay' => 'true',
            ]);
        }

        /** @var Response $response */
        $response = $next($request);

        try {
            IdempotencyKey::query()->create([
                'key' => $key,
                'user_id' => $userId,
                'method' => $method,
                'path' => $path,
                'response_status' => $response->getStatusCode(),
                'response_body' => json_decode($response->getContent() ?: '{}', true),
            ]);
        } catch (QueryException) {
            // Duas requisicoes concorrentes com a mesma chave: a outra ja gravou o registro.
        }

        return $response;
    }
}
