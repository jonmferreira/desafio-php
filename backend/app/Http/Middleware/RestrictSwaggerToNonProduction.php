<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RestrictSwaggerToNonProduction
{
    /**
     * Esconde a documentacao Swagger em producao (Improper Inventory Management - API9).
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production')) {
            throw new NotFoundHttpException;
        }

        return $next($request);
    }
}
