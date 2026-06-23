<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['lote_id', 'product_id', 'quantidade_fardos', 'itens_por_fardo', 'valor_unitario'])]
class LoteItem extends Model
{
    protected $primaryKey = null;

    public $incrementing = false;

    protected $appends = ['subtotal'];

    protected function casts(): array
    {
        return [
            'quantidade_fardos' => 'integer',
            'itens_por_fardo' => 'integer',
            'valor_unitario' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Lote, $this>
     */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->valor_unitario * $this->itens_por_fardo * $this->quantidade_fardos);
    }

    public function getPesoItemAttribute(): float
    {
        return (float) (($this->product->peso ?? 0) * $this->itens_por_fardo * $this->quantidade_fardos);
    }
}
