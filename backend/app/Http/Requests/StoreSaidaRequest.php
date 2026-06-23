<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaidaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'lote_id' => ['required', 'integer', 'exists:lotes,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantidade_fardos' => ['required', 'integer', 'min:1', 'max:1000000'],
            'motivo' => ['nullable', 'string', 'max:500'],
        ];
    }
}
