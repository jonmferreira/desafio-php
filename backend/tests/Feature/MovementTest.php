<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovementTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->operator = User::factory()->create();
        $this->product = Product::factory()->create([
            'category_id'  => Category::factory()->create()->id,
            'min_quantity' => 5,
        ]);
    }

    private function postMovement(array $payload, string $key = 'key-default'): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->operator)
            ->postJson(
                "/api/products/{$this->product->id}/movements",
                $payload,
                ['Idempotency-Key' => $key]
            );
    }

    public function test_entry_increases_stock(): void
    {
        $this->postMovement(['type' => 'in', 'quantity' => 20, 'reason' => 'Compra'])
            ->assertCreated();

        $this->assertEquals(20, $this->product->fresh()->quantity);
    }

    public function test_exit_decreases_stock(): void
    {
        $this->postMovement(['type' => 'in', 'quantity' => 30, 'reason' => 'Entrada'], 'key-in');
        $this->postMovement(['type' => 'out', 'quantity' => 10, 'reason' => 'Venda'], 'key-out')
            ->assertCreated();

        $this->assertEquals(20, $this->product->fresh()->quantity);
    }

    public function test_exit_exceeding_balance_returns_422(): void
    {
        $this->postMovement(['type' => 'in', 'quantity' => 5, 'reason' => 'Entrada'], 'key-in');
        $this->postMovement(['type' => 'out', 'quantity' => 10, 'reason' => 'Saída excessiva'], 'key-out')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_exit_from_empty_stock_returns_422(): void
    {
        $this->postMovement(['type' => 'out', 'quantity' => 1, 'reason' => 'Sem estoque'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_missing_idempotency_key_returns_400(): void
    {
        $this->actingAs($this->operator)
            ->postJson("/api/products/{$this->product->id}/movements", [
                'type' => 'in', 'quantity' => 10, 'reason' => 'Teste',
            ])
            ->assertStatus(400);
    }

    public function test_duplicate_idempotency_key_does_not_create_second_movement(): void
    {
        $payload = ['type' => 'in', 'quantity' => 15, 'reason' => 'Entrada'];

        $first = $this->postMovement($payload, 'idem-key-abc')->assertCreated();
        $second = $this->postMovement($payload, 'idem-key-abc')->assertCreated();

        $this->assertEquals($first->json('id'), $second->json('id'));
        $this->assertEquals('true', $second->headers->get('X-Idempotent-Replay'));
        $this->assertCount(1, StockMovement::all());
    }

    public function test_movement_response_includes_user(): void
    {
        $response = $this->postMovement(
            ['type' => 'in', 'quantity' => 10, 'reason' => 'Teste']
        )->assertCreated();

        $response->assertJsonPath('user.id', $this->operator->id);
    }
}
