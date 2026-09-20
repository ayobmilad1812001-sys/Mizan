<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreStockAdjustmentRequest extends FormRequest
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
            'idempotency_key' => ['required', 'uuid'],
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('status', 'active')],
            'reason' => ['required', 'string', 'min:3', 'max:255'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('status', 'active')],
            'items.*.counted_quantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // stock_adjustment_items has a unique(adjustment, product): catch a repeated
            // product here so the user gets a message instead of a database error.
            $ids = array_filter(array_column($this->input('items', []), 'product_id'));

            if (count($ids) !== count(array_unique($ids))) {
                $validator->errors()->add('items', 'لا يمكن تكرار نفس المنتج في أكثر من سطر.');
            }
        });
    }
}
