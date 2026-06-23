<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Lote;
use App\Models\LoteItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->operator = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    private function productPayload(array $overrides = []): array
    {
        return array_merge([
            'category_id' => $this->category->id,
            'sku' => 'SKU-TEST-001',
            'name' => 'Produto Teste',
            'unit' => 'un',
            'peso' => '1.500',
            'min_fardos' => 5,
            'price' => '29.90',
        ], $overrides);
    }

    public function test_authenticated_user_can_list_products(): void
    {
        Product::factory()->count(3)->create();

        $this->actingAs($this->operator)
            ->getJson('/api/products')
            ->assertOk()
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_unauthenticated_cannot_list_products(): void
    {
        $this->getJson('/api/products')->assertUnauthorized();
    }

    public function test_admin_can_create_product(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/products', $this->productPayload())
            ->assertCreated()
            ->assertJsonPath('sku', 'SKU-TEST-001')
            ->assertJsonPath('min_fardos', 5);
    }

    public function test_operator_cannot_create_product(): void
    {
        $this->actingAs($this->operator)
            ->postJson('/api/products', $this->productPayload())
            ->assertForbidden();
    }

    public function test_create_product_rejects_duplicate_sku(): void
    {
        Product::factory()->create(['sku' => 'SKU-DUP-001', 'category_id' => $this->category->id]);

        $this->actingAs($this->admin)
            ->postJson('/api/products', $this->productPayload(['sku' => 'SKU-DUP-001']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sku']);
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $this->actingAs($this->admin)
            ->putJson("/api/products/{$product->id}", ['name' => 'Nome Atualizado', 'min_fardos' => 10])
            ->assertOk()
            ->assertJsonPath('name', 'Nome Atualizado');
    }

    public function test_operator_cannot_update_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $this->actingAs($this->operator)
            ->putJson("/api/products/{$product->id}", ['name' => 'Tentativa'])
            ->assertForbidden();
    }

    public function test_admin_can_delete_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $this->actingAs($this->admin)
            ->deleteJson("/api/products/{$product->id}")
            ->assertNoContent();

        $this->assertModelMissing($product);
    }

    public function test_operator_cannot_delete_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $this->actingAs($this->operator)
            ->deleteJson("/api/products/{$product->id}")
            ->assertForbidden();
    }

    public function test_filter_by_status_critico_returns_only_low_stock(): void
    {
        // Produto sem fardos em lotes → estoque crítico (min_fardos=10, 0 em lotes)
        $low = Product::factory()->create([
            'category_id' => $this->category->id,
            'min_fardos' => 10,
        ]);

        // Produto com fardos suficientes em lotes → ok
        $ok = Product::factory()->create(['category_id' => $this->category->id, 'min_fardos' => 3]);
        $lote = Lote::factory()->create(['user_id' => $this->operator->id]);
        LoteItem::create([
            'lote_id' => $lote->id,
            'product_id' => $ok->id,
            'quantidade_fardos' => 10,
            'itens_por_fardo' => 12,
            'valor_unitario' => '10.00',
        ]);

        $response = $this->actingAs($this->operator)
            ->getJson('/api/products?status=critico')
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($low->id));
        $this->assertFalse($ids->contains($ok->id));
    }
}
