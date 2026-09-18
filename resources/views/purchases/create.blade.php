@php
    $productsForJs = $products->map(fn ($p) => [
        'id' => $p->id,
        'sku' => $p->sku,
        'name' => $p->name,
        'units' => $p->units->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'purchase_price' => (string) $u->purchase_price,
        ])->values(),
    ]);
@endphp

<x-layouts.app title="أمر شراء جديد" active="purchases.orders">

    <div x-data="purchaseForm()" class="mx-auto max-w-4xl">
        <form method="POST" action="{{ route('purchases.store') }}">
            @csrf

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-bold text-slate-900">بيانات الأمر</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">المورد</label>
                        <select name="supplier_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">اختر المورد</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">ملاحظات (اختياري)</label>
                        <input type="text" name="notes" value="{{ old('notes') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                </div>
            </div>

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900">الأصناف</h2>
                    <button type="button" @click="addLine()"
                            class="flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        <x-icon name="plus" class="size-3.5" /> إضافة صنف
                    </button>
                </div>

                @error('items') <p class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ $message }}</p> @enderror

                <template x-for="(line, index) in lines" :key="line.uid">
                    <div class="mb-3 grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 sm:grid-cols-12 sm:items-end">
                        <div class="sm:col-span-4">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">المنتج</label>
                            <select :name="`items[${index}][product_id]`" x-model.number="line.product_id" @change="line.product_unit_id = ''; line.unit_cost = ''"
                                    required class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm">
                                <option value="">اختر</option>
                                <template x-for="p in products" :key="p.id">
                                    <option :value="p.id" x-text="p.sku + ' — ' + p.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">الوحدة</label>
                            <select :name="`items[${index}][product_unit_id]`" x-model.number="line.product_unit_id"
                                    @change="line.unit_cost = unitsOf(line.product_id).find(u => u.id === line.product_unit_id)?.purchase_price ?? ''"
                                    required class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm">
                                <option value="">اختر</option>
                                <template x-for="u in unitsOf(line.product_id)" :key="u.id">
                                    <option :value="u.id" x-text="u.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">الكمية</label>
                            <input type="number" step="0.001" min="0.001" :name="`items[${index}][quantity]`" x-model="line.quantity" required
                                   class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm nums" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">تكلفة الوحدة</label>
                            <input type="number" step="0.001" min="0" :name="`items[${index}][unit_cost]`" x-model="line.unit_cost" required
                                   class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm nums" />
                        </div>
                        <div class="flex justify-end sm:col-span-1">
                            <button type="button" @click="removeLine(index)" x-show="lines.length > 1"
                                    class="text-slate-400 hover:text-red-600" title="حذف">✕</button>
                        </div>
                    </div>
                </template>

                <p class="mt-3 text-end text-sm text-slate-600">
                    الإجمالي التقديري: <span class="nums font-bold text-slate-900" x-text="estimatedTotal()"></span>
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('purchases.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">إلغاء</a>
                <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">حفظ كمسودة</button>
            </div>
        </form>
    </div>

    <script>
        function purchaseForm() {
            return {
                products: @json($productsForJs),
                lines: [{ uid: 1, product_id: '', product_unit_id: '', quantity: '', unit_cost: '' }],
                nextUid: 2,
                unitsOf(productId) {
                    return this.products.find(p => p.id === productId)?.units ?? [];
                },
                addLine() {
                    this.lines.push({ uid: this.nextUid++, product_id: '', product_unit_id: '', quantity: '', unit_cost: '' });
                },
                removeLine(index) {
                    this.lines.splice(index, 1);
                },
                estimatedTotal() {
                    return this.lines.reduce((sum, l) => sum + (parseFloat(l.quantity) || 0) * (parseFloat(l.unit_cost) || 0), 0).toFixed(3);
                },
            };
        }
    </script>

</x-layouts.app>
