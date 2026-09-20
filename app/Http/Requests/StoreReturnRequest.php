<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prices are absent on purpose: the refund is computed from the net price frozen
     * on the original sale line.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'uuid'],
            'sale_id' => ['required', Rule::exists('sales', 'id')->where('payment_method', 'cash')],
            'reason' => ['required', 'string', 'min:3', 'max:255'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // return_items has a unique(return_id, sale_item_id).
            $ids = array_filter(array_column($this->input('items', []), 'sale_item_id'));

            if (count($ids) !== count(array_unique($ids))) {
                $validator->errors()->add('items', 'لا يمكن تكرار نفس السطر مرتين.');
            }
        });
    }
}
