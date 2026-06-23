<?php

namespace Tests\Feature;

use App\Models\Category;
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
            'category_id'  => $this->category->id,
            'sku'          => 'SKU-TEST-001',
            'name'         => 'Produto Teste',
            'unit'         => 'un',
            'min_quantity' => 5,
            'price'        => '29.90',
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
            ->assertJsonPath('sku', 'SKU-TEST-001');
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
            ->putJson("/api/products/{$product->id}", ['name' => 'Nome Atualizado'])
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

    public function test_filter_by_status_ruptura_returns_only_low_stock(): void
    {
        $low = Product::factory()->create(['category_id' => $this->category->id, 'min_quantity' => 10]);
        $ok = Product::factory()->create(['category_id' => $this->category->id, 'min_quantity' => 1]);

        $ok->stockMovements()->create([
            'user_id'  => $this->operator->id,
            'type'     => 'in',
            'quantity' => 50,
            'reason'   => 'Entrada inicial',
        ]);

        $response = $this->actingAs($this->operator)
            ->getJson('/api/products?status=ruptura')
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($low->id));
        $this->assertFalse($ids->contains($ok->id));
    }
}
