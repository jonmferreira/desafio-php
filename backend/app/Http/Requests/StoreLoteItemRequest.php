<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoteItemRequest extends FormRequest
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantidade_fardos' => ['required', 'integer', 'min:1', 'max:1000000'],
            'itens_por_fardo' => ['required', 'integer', 'min:1', 'max:10000'],
            'valor_unitario' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
        ];
    }
}
