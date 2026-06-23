<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvariaController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LoteController;
use App\Http\Controllers\Api\LoteItemController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SaidaController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Rate limit no login: mitiga brute-force (API2).
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Categorias: leitura pública (autenticada), escrita restrita a admin (API5).
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('admin');

    // Relatórios
    Route::get('/reports/stock-flow', [ReportController::class, 'stockFlow']);
    Route::get('/reports/ruptura', [ReportController::class, 'ruptura']);
    Route::get('/reports/valor-estoque', [ReportController::class, 'valorEstoque']);
    Route::get('/reports/giro', [ReportController::class, 'giro']);
    Route::get('/reports/capital-clientes', [ReportController::class, 'capitalPorCliente']);
    Route::get('/reports/estoque-produtos', [ReportController::class, 'estoquePorProduto']);

    // Produtos: export antes de {product} para não conflitar com route-model binding.
    Route::get('/products/export', [ProductController::class, 'export'])->middleware('admin');
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store'])->middleware('admin');
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('admin');
    Route::patch('/products/{product}', [ProductController::class, 'update'])->middleware('admin');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('admin');

    // Movimentações de estoque: operator pode criar (API5).
    Route::get('/products/{product}/movements', [StockMovementController::class, 'index']);
    Route::post('/products/{product}/movements', [StockMovementController::class, 'store'])
        ->middleware('idempotent');

    // Lotes: admin cria (user_id livre), operator só vê/cria seus próprios.
    Route::get('/lotes', [LoteController::class, 'index']);
    Route::post('/lotes', [LoteController::class, 'store']);
    Route::get('/lotes/{lote}', [LoteController::class, 'show']);
    Route::get('/lotes/{lote}/volume', [LoteController::class, 'volume']);
    Route::delete('/lotes/{lote}', [LoteController::class, 'destroy'])->middleware('admin');

    // Itens de lote
    Route::post('/lotes/{lote}/items', [LoteItemController::class, 'store']);
    Route::put('/lotes/{lote}/items/{product}', [LoteItemController::class, 'update']);
    Route::delete('/lotes/{lote}/items/{product}', [LoteItemController::class, 'destroy']);

    // Avarias
    Route::post('/lotes/{lote}/avarias', [AvariaController::class, 'store']);
    Route::delete('/lotes/{lote}/avarias/{avaria}', [AvariaController::class, 'destroy']);

    // Saídas
    Route::post('/saidas', [SaidaController::class, 'store']);

    // Usuários: listagem e criação restritas a admin; consulta/edição individual via UserPolicy.
    Route::get('/users', [UserController::class, 'index'])->middleware('admin');
    Route::post('/users', [UserController::class, 'store'])->middleware('admin');
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::get('/users/{user}/movements', [UserController::class, 'movements']);
});
