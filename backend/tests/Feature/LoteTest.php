<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Lote;
use App\Models\LoteItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin    = User::factory()->admin()->create();
        $this->operator = User::factory()->create();
        $this->product  = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
            'min_fardos'  => 5,
        ]);
    }

    private function createLoteWithItem(User $user, int $fardos = 10): Lote
    {
        $lote = Lote::factory()->create(['user_id' => $user->id]);
        LoteItem::create([
            'lote_id'          => $lote->id,
            'product_id'       => $this->product->id,
            'quantidade_fardos' => $fardos,
            'itens_por_fardo'  => 12,
            'valor_unitario'   => '25.90',
        ]);

        return $lote;
    }

    // --- CRUD Lotes ---

    public function test_authenticated_user_can_list_lotes(): void
    {
        Lote::factory()->count(3)->create();

        $this->actingAs($this->operator)
            ->getJson('/api/lotes')
            ->assertOk()
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_unauthenticated_cannot_list_lotes(): void
    {
        $this->getJson('/api/lotes')->assertUnauthorized();
    }

    public function test_operator_can_create_lote(): void
    {
        $this->actingAs($this->operator)
            ->postJson('/api/lotes', ['user_id' => $this->operator->id, 'frete' => '35.00'])
            ->assertCreated()
            ->assertJsonPath('user_id', $this->operator->id);
    }

    public function test_lote_numero_auto_increments_per_user(): void
    {
        $this->actingAs($this->operator)
            ->postJson('/api/lotes', ['user_id' => $this->operator->id, 'frete' => '0.00']);

        $response = $this->actingAs($this->operator)
            ->postJson('/api/lotes', ['user_id' => $this->operator->id, 'frete' => '0.00'])
            ->assertCreated();

        $this->assertEquals(2, $response->json('numero'));
    }

    public function test_lote_numero_is_independent_per_user(): void
    {
        $other = User::factory()->create();

        $this->actingAs($this->admin)
            ->postJson('/api/lotes', ['user_id' => $this->operator->id, 'frete' => '0.00']);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/lotes', ['user_id' => $other->id, 'frete' => '0.00'])
            ->assertCreated();

        $this->assertEquals(1, $response->json('numero'), 'Numeração de outro usuário começa em 1');
    }

    public function test_show_lote_includes_itens_and_totals(): void
    {
        $lote = $this->createLoteWithItem($this->operator);

        $response = $this->actingAs($this->operator)
            ->getJson("/api/lotes/{$lote->id}")
            ->assertOk()
            ->assertJsonStructure(['lote', 'total_itens', 'total_avarias', 'total', 'peso_total']);

        $this->assertIsArray($response->json('lote.itens'));
    }

    public function test_admin_can_delete_lote(): void
    {
        $lote = Lote::factory()->create(['user_id' => $this->operator->id]);

        $this->actingAs($this->admin)
            ->deleteJson("/api/lotes/{$lote->id}")
            ->assertNoContent();

        $this->assertModelMissing($lote);
    }

    public function test_operator_cannot_delete_lote(): void
    {
        $lote = Lote::factory()->create(['user_id' => $this->operator->id]);

        $this->actingAs($this->operator)
            ->deleteJson("/api/lotes/{$lote->id}")
            ->assertForbidden();
    }

    // --- Lote Items ---

    public function test_can_add_item_to_lote(): void
    {
        $lote = Lote::factory()->create(['user_id' => $this->operator->id]);

        $this->actingAs($this->operator)
            ->postJson("/api/lotes/{$lote->id}/items", [
                'product_id'       => $this->product->id,
                'quantidade_fardos' => 10,
                'itens_por_fardo'  => 12,
                'valor_unitario'   => '25.90',
            ])
            ->assertCreated()
            ->assertJsonPath('quantidade_fardos', 10);
    }

    public function test_item_subtotal_is_calculated_correctly(): void
    {
        $lote = Lote::factory()->create(['user_id' => $this->operator->id]);

        $response = $this->actingAs($this->operator)
            ->postJson("/api/lotes/{$lote->id}/items", [
                'product_id'       => $this->product->id,
                'quantidade_fardos' => 5,
                'itens_por_fardo'  => 10,
                'valor_unitario'   => '20.00',
            ])
            ->assertCreated();

        // 5 fardos × 10 itens × R$20 = R$1.000
        $this->assertEquals(1000.0, (float) $response->json('subtotal'));
    }

    public function test_can_update_item_quantidade(): void
    {
        $lote = $this->createLoteWithItem($this->operator, 10);

        $this->actingAs($this->operator)
            ->putJson("/api/lotes/{$lote->id}/items/{$this->product->id}", [
                'quantidade_fardos' => 3,
            ])
            ->assertOk()
            ->assertJsonPath('quantidade_fardos', 3);
    }

    public function test_can_remove_item_from_lote(): void
    {
        $lote = $this->createLoteWithItem($this->operator);

        $this->actingAs($this->operator)
            ->deleteJson("/api/lotes/{$lote->id}/items/{$this->product->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('lote_items', [
            'lote_id'    => $lote->id,
            'product_id' => $this->product->id,
        ]);
    }

    // --- Avarias ---

    public function test_can_add_avaria_to_lote(): void
    {
        $lote = Lote::factory()->create(['user_id' => $this->operator->id]);

        $this->actingAs($this->operator)
            ->postJson("/api/lotes/{$lote->id}/avarias", [
                'descricao' => 'Caixas amassadas',
                'valor'     => '45.00',
            ])
            ->assertCreated()
            ->assertJsonPath('descricao', 'Caixas amassadas');
    }

    public function test_can_remove_avaria(): void
    {
        $lote   = Lote::factory()->create(['user_id' => $this->operator->id]);
        $avaria = $lote->avarias()->create(['descricao' => 'Avaria teste', 'valor' => '10.00']);

        $this->actingAs($this->operator)
            ->deleteJson("/api/lotes/{$lote->id}/avarias/{$avaria->id}")
            ->assertNoContent();

        $this->assertModelMissing($avaria);
    }

    // --- Saídas ---

    public function test_saida_decrements_fardos_no_lote(): void
    {
        $lote = $this->createLoteWithItem($this->operator, 10);

        $this->actingAs($this->operator)
            ->postJson('/api/saidas', [
                'lote_id'          => $lote->id,
                'product_id'       => $this->product->id,
                'quantidade_fardos' => 3,
                'motivo'           => 'Venda balcão',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('lote_items', [
            'lote_id'          => $lote->id,
            'product_id'       => $this->product->id,
            'quantidade_fardos' => 7,
        ]);
    }

    public function test_saida_exceeding_available_fardos_returns_422(): void
    {
        $lote = $this->createLoteWithItem($this->operator, 5);

        $this->actingAs($this->operator)
            ->postJson('/api/saidas', [
                'lote_id'          => $lote->id,
                'product_id'       => $this->product->id,
                'quantidade_fardos' => 99,
                'motivo'           => 'Tentativa inválida',
            ])
            ->assertUnprocessable();
    }

    public function test_saida_from_empty_lote_item_returns_422(): void
    {
        $lote = $this->createLoteWithItem($this->operator, 0);

        $this->actingAs($this->operator)
            ->postJson('/api/saidas', [
                'lote_id'          => $lote->id,
                'product_id'       => $this->product->id,
                'quantidade_fardos' => 1,
            ])
            ->assertUnprocessable();
    }

    public function test_saida_response_includes_lote_and_product(): void
    {
        $lote = $this->createLoteWithItem($this->operator, 10);

        $response = $this->actingAs($this->operator)
            ->postJson('/api/saidas', [
                'lote_id'          => $lote->id,
                'product_id'       => $this->product->id,
                'quantidade_fardos' => 2,
                'motivo'           => 'Entrega',
            ])
            ->assertCreated();

        $response->assertJsonPath('lote_id', $lote->id);
        $response->assertJsonPath('product_id', $this->product->id);
        $response->assertJsonPath('quantidade_fardos', 2);
    }
}
