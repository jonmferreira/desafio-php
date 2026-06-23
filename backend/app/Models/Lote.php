<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'frete'])]
class Lote extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'frete' => 'decimal:2',
            'numero' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Lote $lote): void {
            $lote->numero = (static::where('user_id', $lote->user_id)->max('numero') ?? 0) + 1;
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<LoteItem, $this>
     */
    public function itens(): HasMany
    {
        return $this->hasMany(LoteItem::class);
    }

    /**
     * @return HasMany<Avaria, $this>
     */
    public function avarias(): HasMany
    {
        return $this->hasMany(Avaria::class);
    }

    /**
     * @return HasMany<Saida, $this>
     */
    public function saidas(): HasMany
    {
        return $this->hasMany(Saida::class);
    }
}
