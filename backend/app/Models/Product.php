<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_id', 'sku', 'name', 'description', 'unit', 'peso', 'min_fardos', 'price'])]
class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'peso'       => 'decimal:3',
            'min_fardos' => 'integer',
            'price'      => 'decimal:2',
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
     * @return HasMany<LoteItem, $this>
     */
    public function loteItems(): HasMany
    {
        return $this->hasMany(LoteItem::class);
    }
}
