<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Lote;
use App\Models\LoteItem;
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
        $this->admin    = User::factory()->admin()->create();
        $this->operator = User::factory()->create();
    }

    private function addLoteItem(Lote $lote, Product $product, int $fardos, int $itensPorFardo = 12, string $valor = '10.00'): void
    {
        LoteItem::create([
            'lote_id'          => $lote->id,
            'product_id'       => $product->id,
            'quantidade_fardos' => $fardos,
            'itens_por_fardo'  => $itensPorFardo,
            'valor_unitario'   => $valor,
        ]);
    }

    // --- Ruptura ---

    public function test_ruptura_returns_only_products_below_minimum(): void
    {
        $category = Category::factory()->create();

        $low = Product::factory()->create(['category_id' => $category->id, 'min_fardos' => 10]);

        $ok   = Product::factory()->create(['category_id' => $category->id, 'min_fardos' => 3]);
        $lote = Lote::factory()->create(['user_id' => $this->operator->id]);
        $this->addLoteItem($lote, $ok, 10);

        $response = $this->actingAs($this->operator)
            ->getJson('/api/reports/ruptura')
            ->assertOk()
            ->assertJsonStructure([['id', 'sku', 'name', 'min_fardos', 'total_fardos', 'category']]);

        $ids = collect($response->json())->pluck('id');
        $this->assertTrue($ids->contains($low->id), 'Produto sem fardos deve aparecer na ruptura');
        $this->assertFalse($ids->contains($ok->id), 'Produto com fardos suficientes não deve aparecer');
    }

    public function test_ruptura_is_empty_when_all_products_have_adequate_stock(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id, 'min_fardos' => 5]);
        $lote     = Lote::factory()->create(['user_id' => $this->operator->id]);
        $this->addLoteItem($lote, $product, 20);

        $this->actingAs($this->operator)
            ->getJson('/api/reports/ruptura')
            ->assertOk()
            ->assertExactJson([]);
    }

    // --- Capital por cliente ---

    public function test_capital_clientes_returns_sum_per_user(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $lote = Lote::factory()->create(['user_id' => $this->operator->id]);
        // capital = 5 fardos × 10 itens × R$20 = R$1.000
        $this->addLoteItem($lote, $product, 5, 10, '20.00');

        $response = $this->actingAs($this->admin)
            ->getJson('/api/reports/capital-clientes')
            ->assertOk()
            ->assertJsonStructure([['id', 'name', 'capital']]);

        $row = collect($response->json())->firstWhere('id', $this->operator->id);
        $this->assertNotNull($row);
        $this->assertEquals(1000.0, (float) $row['capital'], '5 × 10 × R$20 = R$1.000');
    }

    public function test_capital_clientes_shows_zero_for_users_without_lotes(): void
    {
        $semLote = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/reports/capital-clientes')
            ->assertOk();

        $row = collect($response->json())->firstWhere('id', $semLote->id);
        $this->assertNotNull($row);
        $this->assertEquals(0.0, (float) $row['capital']);
    }

    // --- Estoque por produto ---

    public function test_estoque_produtos_returns_fardos_per_product(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);
        $lote     = Lote::factory()->create(['user_id' => $this->operator->id]);
        $this->addLoteItem($lote, $product, 8);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/reports/estoque-produtos')
            ->assertOk()
            ->assertJsonStructure([['id', 'sku', 'name', 'unit', 'min_fardos', 'total_fardos']]);

        $row = collect($response->json())->firstWhere('id', $product->id);
        $this->assertNotNull($row);
        $this->assertEquals(8, $row['total_fardos']);
    }

    // --- Giro (stock_movements) ---

    public function test_giro_returns_top_products_by_quantity(): void
    {
        $category = Category::factory()->create();
        $top      = Product::factory()->create(['category_id' => $category->id]);
        $low      = Product::factory()->create(['category_id' => $category->id]);

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
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $this->travel(-31)->days();
        $product->stockMovements()->create([
            'user_id' => $this->operator->id, 'type' => 'in', 'quantity' => 100, 'reason' => 'Antiga',
        ]);
        $this->travelBack();

        $this->actingAs($this->operator)
            ->getJson('/api/reports/giro')
            ->assertOk()
            ->assertExactJson([]);
    }

    // --- Autenticação ---

    public function test_unauthenticated_cannot_access_reports(): void
    {
        $this->getJson('/api/reports/ruptura')->assertUnauthorized();
        $this->getJson('/api/reports/capital-clientes')->assertUnauthorized();
        $this->getJson('/api/reports/estoque-produtos')->assertUnauthorized();
        $this->getJson('/api/reports/giro')->assertUnauthorized();
    }
}
