@php
    // Computed before the component so the @json calls below stay simple: Blade's
    // directive parser mis-reads a nested multi-line closure passed straight to @json.
    $productsForJs = $products->map(fn ($p) => [
        'id' => $p->id,
        'sku' => $p->sku,
        'name' => $p->name,
        'base_unit' => $p->baseUnit?->name ?? '',
    ]);
@endphp

<x-layouts.app title="تسوية مخزون جديدة" active="inventory.adjustments">

    <div x-data="adjustmentForm()" class="mx-auto max-w-4xl">
        <form method="POST" action="{{ route('stock-adjustments.store') }}">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) Str::uuid()) }}">

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-bold text-slate-900">بيانات التسوية</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">المخزن</label>
                        <select name="warehouse_id" x-model.number="warehouseId" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">اختر المخزن</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        @error('warehouse_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">سبب التسوية</label>
                        <input type="text" name="reason" value="{{ old('reason') }}" required placeholder="جرد دوري، تالف، فقد..."
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900">الأصناف المعدودة</h2>
                    <button type="button" @click="addLine()"
                            class="flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        <x-icon name="plus" class="size-3.5" /> إضافة صنف
                    </button>
                </div>

                @error('items') <p class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ $message }}</p> @enderror

                <div class="mb-3 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
                    أدخل الكمية التي عددتها فعلياً بالوحدة الأساسية. الأصناف المطابقة للرصيد لن تُسجَّل.
                </div>

                <template x-for="(line, index) in lines" :key="line.uid">
                    <div class="mb-3 grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 sm:grid-cols-12 sm:items-end">
                        <div class="sm:col-span-5">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">المنتج</label>
                            <select :name="`items[${index}][product_id]`" x-model.number="line.product_id" required
                                    class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm">
                                <option value="">اختر</option>
                                <template x-for="p in products" :key="p.id">
                                    <option :value="p.id" x-text="p.sku + ' — ' + p.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">الرصيد الحالي</label>
                            <p class="nums px-1 py-1.5 text-sm text-slate-700" x-text="currentBalance(line.product_id)"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">الكمية المعدودة</label>
                            <input type="number" step="0.001" min="0" :name="`items[${index}][counted_quantity]`" x-model="line.counted_quantity" required
                                   class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm nums" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">الفرق</label>
                            <p class="nums px-1 py-1.5 text-sm font-semibold"
                               :class="differenceClass(line)" x-text="difference(line)"></p>
                        </div>
                        <div class="flex justify-end sm:col-span-1">
                            <button type="button" @click="removeLine(index)" x-show="lines.length > 1"
                                    class="text-slate-400 hover:text-red-600" title="حذف">✕</button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('stock-adjustments.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">إلغاء</a>
                <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">حفظ التسوية</button>
            </div>
        </form>
    </div>

    <script>
        function adjustmentForm() {
            return {
                products: @json($productsForJs),
                balances: @json($balances),
                warehouseId: {{ (int) old('warehouse_id') }} || '',
                lines: [{ uid: 1, product_id: '', counted_quantity: '' }],
                nextUid: 2,
                currentBalance(productId) {
                    if (!this.warehouseId || !productId) return '—';
                    return this.balances[`${this.warehouseId}:${productId}`] ?? '0.000';
                },
                difference(line) {
                    const current = this.currentBalance(line.product_id);
                    if (current === '—' || line.counted_quantity === '') return '—';
                    const diff = parseFloat(line.counted_quantity) - parseFloat(current);
                    return (diff > 0 ? '+' : '') + diff.toFixed(3);
                },
                differenceClass(line) {
                    const value = this.difference(line);
                    if (value === '—' || parseFloat(value) === 0) return 'text-slate-400';
                    return parseFloat(value) > 0 ? 'text-emerald-600' : 'text-red-600';
                },
                addLine() {
                    this.lines.push({ uid: this.nextUid++, product_id: '', counted_quantity: '' });
                },
                removeLine(index) {
                    this.lines.splice(index, 1);
                },
            };
        }
    </script>

</x-layouts.app>
