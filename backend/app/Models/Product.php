<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_id', 'sku', 'name', 'description', 'unit', 'min_quantity', 'price'])]
class Product extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $appends = ['quantity'];

    protected function casts(): array
    {
        return [
            'min_quantity' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Saldo atual em estoque: soma de entradas menos soma de saidas.
     */
    public function getQuantityAttribute(): int
    {
        return (int) $this->stockMovements()
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'in' THEN quantity ELSE -quantity END), 0) as balance")
            ->value('balance');
    }
}
