<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Models\ProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Note what is absent: no prices. The screen sends units and quantities only;
     * the service reads every price from the database.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'uuid'],
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('status', 'active')],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('status', 'active')],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'discount_percent' => ['nullable', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string', 'max:255'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_unit_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->input('items', []) as $index => $item) {
                if (empty($item['product_unit_id'])) {
                    continue;
                }

                $unit = ProductUnit::with('product')->find($item['product_unit_id']);
                $line = $index + 1;

                if (! $unit) {
                    $validator->errors()->add('items', "السطر {$line}: وحدة غير معروفة.");

                    continue;
                }

                if (! $unit->isActive() || ! $unit->product->isActive()) {
                    $validator->errors()->add('items', "السطر {$line}: \"{$unit->product->name}\" أو وحدته موقوفة ولا يمكن بيعها.");
                }
            }
        });
    }
}
