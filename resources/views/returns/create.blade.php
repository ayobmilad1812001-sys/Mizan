@php
    $linesForJs = $sale->items->map(fn ($item) => [
        'id' => $item->id,
        'name' => $item->product->name,
        'unit' => $item->unit->name,
        'sold' => (string) $item->quantity,
        'returned' => (string) $item->returned_quantity,
        'remaining' => $item->returnableQuantity(),
        'net_price' => (string) $item->net_unit_price,
    ]);
@endphp

<x-layouts.app title="إرجاع من {{ $sale->invoice_number }}" active="sales.returns">

    <div x-data="returnForm()" class="mx-auto max-w-4xl">
        <form method="POST" action="{{ route('returns.store') }}">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) Str::uuid()) }}">
            <input type="hidden" name="sale_id" value="{{ $sale->id }}">

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="nums text-lg font-bold text-slate-900">{{ $sale->invoice_number }}</p>
                        <p class="text-sm text-slate-500">
                            {{ $sale->customer?->name ?? 'زبون نقدي' }} — {{ $sale->warehouse->name }}
                        </p>
                    </div>
                    <p class="nums text-sm text-slate-500">{{ $sale->created_at->format('Y-m-d H:i') }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">سبب الإرجاع</label>
                    <input type="text" name="reason" value="{{ old('reason') }}" required placeholder="تالف، لم يعجب الزبون، صنف خاطئ..."
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                    @error('reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <p class="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
                    يُرَدّ المبلغ نقداً من درج جلستك الحالية، بسعر الصنف بعد الخصم. اترك الكمية صفراً للأصناف التي لا تُرجَع.
                </p>
            </div>

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-bold text-slate-900">أصناف الفاتورة</h2>

                @error('items') <p class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ $message }}</p> @enderror

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-[11px] uppercase text-slate-500">
                            <tr>
                                <th class="pb-2 text-start">المنتج</th>
                                <th class="pb-2 text-start">الوحدة</th>
                                <th class="pb-2 text-center">المُباع</th>
                                <th class="pb-2 text-center">أُرجع سابقاً</th>
                                <th class="pb-2 text-center">المتبقي</th>
                                <th class="pb-2 text-center">سعر الإرجاع</th>
                                <th class="pb-2 text-center">كمية الإرجاع</th>
                                <th class="pb-2 text-center">المبلغ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(line, index) in lines" :key="line.id">
                                <tr :class="parseFloat(line.remaining) <= 0 && 'opacity-40'">
                                    <td class="py-2 text-slate-800" x-text="line.name"></td>
                                    <td class="py-2 text-slate-600" x-text="line.unit"></td>
                                    <td class="nums py-2 text-center text-slate-600" x-text="line.sold"></td>
                                    <td class="nums py-2 text-center text-slate-500" x-text="line.returned"></td>
                                    <td class="nums py-2 text-center font-semibold text-slate-800" x-text="line.remaining"></td>
                                    <td class="nums py-2 text-center text-slate-600" x-text="line.net_price"></td>
                                    <td class="py-2 text-center">
                                        <input type="hidden" :name="`items[${index}][sale_item_id]`" :value="line.id">
                                        <input type="number" step="0.001" min="0" :max="line.remaining"
                                               :name="`items[${index}][quantity]`" x-model="line.quantity"
                                               :disabled="parseFloat(line.remaining) <= 0"
                                               class="nums w-24 rounded-lg border border-slate-300 px-2 py-1 text-center text-sm disabled:bg-slate-100" />
                                    </td>
                                    <td class="nums py-2 text-center font-semibold" :class="lineTotal(line) > 0 ? 'text-slate-900' : 'text-slate-400'"
                                        x-text="lineTotal(line).toFixed(3)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <p :class="! lines.some(l => overLimit(l)) && 'hidden'"
                   class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">
                    كمية إرجاع أكبر من المتبقي — سيرفض النظام الحفظ.
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-slate-600">
                    المبلغ المُعاد نقداً:
                    <span class="nums text-lg font-bold text-brand-700" x-text="total().toFixed(3)"></span>
                </p>
                <div class="flex gap-3">
                    <a href="{{ route('sales.show', $sale) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">إلغاء</a>
                    <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                        تأكيد الإرجاع وردّ المبلغ
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function returnForm() {
            return {
                lines: @json($linesForJs).map(l => ({ ...l, quantity: '0' })),
                lineTotal(line) {
                    const qty = parseFloat(line.quantity) || 0;
                    if (qty <= 0) return 0;
                    return Math.round(qty * parseFloat(line.net_price) * 1000) / 1000;
                },
                overLimit(line) {
                    return (parseFloat(line.quantity) || 0) > parseFloat(line.remaining);
                },
                total() {
                    return this.lines.reduce((sum, line) => sum + this.lineTotal(line), 0);
                },
            };
        }
    </script>

</x-layouts.app>
