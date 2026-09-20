<?php

namespace App\Http\Requests;

use App\Models\ProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreStockTransferRequest extends FormRequest
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
            'from_warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('status', 'active')],
            'to_warehouse_id' => ['required', 'different:from_warehouse_id', Rule::exists('warehouses', 'id')->where('status', 'active')],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('status', 'active')],
            'items.*.product_unit_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // stock_transfer_items has a unique(transfer, product).
            $ids = array_filter(array_column($this->input('items', []), 'product_id'));

            if (count($ids) !== count(array_unique($ids))) {
                $validator->errors()->add('items', 'لا يمكن تكرار نفس المنتج في أكثر من سطر.');
            }

            foreach ($this->input('items', []) as $index => $item) {
                if (empty($item['product_id']) || empty($item['product_unit_id'])) {
                    continue;
                }

                $unit = ProductUnit::find($item['product_unit_id']);

                $line = $index + 1;

                if (! $unit || $unit->product_id !== (int) $item['product_id']) {
                    $validator->errors()->add('items', "السطر {$line}: الوحدة المختارة لا تتبع هذا المنتج.");

                    continue;
                }

                if (! $unit->isActive()) {
                    $validator->errors()->add('items', "السطر {$line}: هذه الوحدة موقوفة.");
                }
            }
        });
    }
}
