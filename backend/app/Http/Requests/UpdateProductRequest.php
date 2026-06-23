<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('product');

        return [
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'sku' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($productId)],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'unit' => ['sometimes', 'required', 'string', 'max:10'],
            'peso' => ['sometimes', 'required', 'numeric', 'min:0', 'max:99999.999'],
            'min_fardos' => ['sometimes', 'required', 'integer', 'min:0', 'max:1000000'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0', 'max:999999999.99'],
        ];
    }
}
