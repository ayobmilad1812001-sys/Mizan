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
        return [
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('status', 'active')],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('status', 'active')],
            'description' => ['nullable', 'string'],
            'reorder_level' => ['required', 'numeric', 'min:0'],
        ];
    }
}
