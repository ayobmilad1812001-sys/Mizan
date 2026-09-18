<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProductRequest extends FormRequest
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
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('status', 'active')],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('status', 'active')],
            'description' => ['nullable', 'string'],
            'reorder_level' => ['required', 'numeric', 'min:0'],

            'units' => ['required', 'array', 'min:1'],
            'units.*.name' => ['required', 'string', 'max:30'],
            'units.*.factor' => ['required', 'numeric', 'gt:0'],
            'units.*.is_base' => ['nullable', 'boolean'],
            'units.*.barcode' => ['nullable', 'string', 'max:50', 'distinct', 'unique:product_units,barcode'],
            'units.*.purchase_price' => ['required', 'numeric', 'min:0'],
            'units.*.selling_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $units = collect($this->input('units', []));

            $baseUnits = $units->filter(fn ($u) => (bool) ($u['is_base'] ?? false));

            if ($baseUnits->count() !== 1) {
                $validator->errors()->add('units', 'يجب تحديد وحدة أساس واحدة فقط.');

                return;
            }

            if ((float) $baseUnits->first()['factor'] !== 1.0) {
                $validator->errors()->add('units', 'معامل وحدة الأساس يجب أن يساوي 1.');
            }

            $names = $units->pluck('name')->map(fn ($n) => mb_strtolower(trim((string) $n)));

            if ($names->count() !== $names->unique()->count()) {
                $validator->errors()->add('units', 'أسماء الوحدات يجب ألا تتكرر.');
            }
        });
    }
}
