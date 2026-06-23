<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->operator = User::factory()->create();
    }

    public function test_ruptura_returns_only_products_below_minimum(): void
    {
        $category = Category::factory()->create();

        $low = Product::factory()->create(['category_id' => $category->id, 'min_quantity' => 10]);

        $ok = Product::factory()->create(['category_id' => $category->id, 'min_quantity' => 5]);
        $ok->stockMovements()->create([
            'user_id' => $this->operator->id, 'type' => 'in', 'quantity' => 20, 'reason' => 'Entrada',
        ]);

        $response = $this->actingAs($this->operator)
            ->getJson('/api/reports/ruptura')
            ->assertOk()
            ->assertJsonStructure([['id', 'sku', 'name', 'min_quantity', 'balance', 'category']]);

        $ids = collect($response->json())->pluck('id');
        $this->assertTrue($ids->contains($low->id), 'Produto sem estoque deve aparecer na ruptura');
        $this->assertFalse($ids->contains($ok->id), 'Produto com estoque adequado não deve aparecer');
    }

    public function test_ruptura_is_empty_when_all_products_have_adequate_stock(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'min_quantity' => 5]);
        $product->stockMovements()->create([
            'user_id' => $this->operator->id, 'type' => 'in', 'quantity' => 100, 'reason' => 'Entrada',
        ]);

        $this->actingAs($this->operator)
            ->getJson('/api/reports/ruptura')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_valor_estoque_returns_categories_with_value(): void
    {
        $category = Category::factory()->create(['name' => 'Eletrônicos']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price'       => '100.00',
        ]);
        $product->stockMovements()->create([
            'user_id' => $this->operator->id, 'type' => 'in', 'quantity' => 10, 'reason' => 'Entrada',
        ]);

        $response = $this->actingAs($this->operator)
            ->getJson('/api/reports/valor-estoque')
            ->assertOk()
            ->assertJsonStructure([['id', 'category', 'product_count', 'total_value']]);

        $row = collect($response->json())->firstWhere('id', $category->id);
        $this->assertNotNull($row);
        $this->assertEquals(1000.0, (float) $row['total_value'], 'R$100 × 10 un = R$1.000');
    }

    public function test_valor_estoque_does_not_count_negative_balance(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price'       => '50.00',
        ]);

        $response = $this->actingAs($this->operator)
            ->getJson('/api/reports/valor-estoque')
            ->assertOk();

        $row = collect($response->json())->firstWhere('id', $category->id);
        $this->assertEquals(0.0, (float) $row['total_value'], 'Produto sem estoque contribui R$0');
    }

    public function test_giro_returns_top_products_by_quantity(): void
    {
        $category = Category::factory()->create();
        $top = Product::factory()->create(['category_id' => $category->id]);
        $low = Product::factory()->create(['category_id' => $category->id]);

        $top->stockMovements()->create([
            'user_id' => $this->operator->id, 'type' => 'in', 'quantity' => 200, 'reason' => 'Compra',
        ]);
        $low->stockMovements()->create([
            'user_id' => $this->operator->id, 'type' => 'in', 'quantity' => 10, 'reason' => 'Compra',
        ]);

        $response = $this->actingAs($this->operator)
            ->getJson('/api/reports/giro')
            ->assertOk()
            ->assertJsonStructure([['id', 'sku', 'name', 'operation_count', 'total_quantity']]);

        $ids = collect($response->json())->pluck('id')->all();
        $this->assertEquals($top->id, $ids[0], 'Produto com maior giro deve aparecer primeiro');
    }

    public function test_giro_excludes_movements_older_than_30_days(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        // created_at não é fillable: usamos time-travel para Eloquent gravar a data correta
        $this->travel(-31)->days();
        $product->stockMovements()->create([
            'user_id'  => $this->operator->id,
            'type'     => 'in',
            'quantity' => 100,
            'reason'   => 'Antiga',
        ]);
        $this->travelBack();

        $this->actingAs($this->operator)
            ->getJson('/api/reports/giro')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_unauthenticated_cannot_access_reports(): void
    {
        $this->getJson('/api/reports/ruptura')->assertUnauthorized();
        $this->getJson('/api/reports/valor-estoque')->assertUnauthorized();
        $this->getJson('/api/reports/giro')->assertUnauthorized();
    }
}
