<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Limite de per_page protege contra Unrestricted Resource Consumption (API4).
        $perPage = min((int) $request->query('per_page', 15), 100);

        $fardosSql = '(SELECT COALESCE(SUM(li.quantidade_fardos), 0)'
            .' FROM lote_items li WHERE li.product_id = products.id)';

        $query = Product::query()
            ->with('category')
            ->when($request->query('category_id'), fn ($q, $v) => $q->where('category_id', $v))
            ->when($request->query('price_min'), fn ($q, $v) => $q->where('price', '>=', $v))
            ->when($request->query('price_max'), fn ($q, $v) => $q->where('price', '<=', $v))
            ->when($request->query('status') === 'critico', fn ($q) => $q->whereRaw("$fardosSql < products.min_fardos"))
            ->when($request->query('status') === 'ok', fn ($q) => $q->whereRaw("$fardosSql >= products.min_fardos"));

        return response()->json($query->paginate($perPage));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::query()->create($request->validated());

        return response()->json($product->load('category'), 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json($product->load('category'));
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return response()->json($product->load('category'));
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(null, 204);
    }

    public function export(): Response
    {
        $products = Product::query()->with('category')->get();

        $escape = fn (mixed $v) => '"' . str_replace('"', '""', (string) $v) . '"';

        $lines = [
            implode(';', array_map($escape, ['SKU', 'Nome', 'Categoria', 'Unidade', 'Peso (kg)', 'Fardos Mínimos', 'Fardos em Estoque', 'Preço'])),
        ];

        foreach ($products as $product) {
            $fardos = $product->loteItems()->sum('quantidade_fardos');
            $lines[] = implode(';', array_map($escape, [
                $product->sku,
                $product->name,
                $product->category?->name ?? '',
                $product->unit,
                number_format((float) $product->peso, 3, ',', '.'),
                $product->min_fardos,
                $fardos,
                number_format((float) $product->price, 2, ',', '.'),
            ]));
        }

        $csv = "\xEF\xBB\xBF" . implode("\n", $lines);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="produtos-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
