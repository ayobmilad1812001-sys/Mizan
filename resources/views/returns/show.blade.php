<x-layouts.app title="المرتجع {{ $return->reference_number }}" active="sales.returns">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800 print:hidden">
            {{ session('status') }}
        </div>
    @endif

    <div class="mx-auto max-w-3xl space-y-5">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="nums text-lg font-bold text-slate-900">{{ $return->reference_number }}</p>
                    <p class="text-sm text-slate-500">
                        مرتجع من الفاتورة
                        <a href="{{ route('sales.show', $return->sale) }}" class="nums font-medium text-brand-700 hover:underline">{{ $return->sale->invoice_number }}</a>
                    </p>
                </div>
                <button type="button" onclick="window.print()"
                        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 print:hidden">
                    طباعة
                </button>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                <div>
                    <p class="text-[11px] text-slate-500">العميل</p>
                    <p class="text-slate-800">{{ $return->sale->customer?->name ?? 'زبون نقدي' }}</p>
                </div>
                <div>
                    <p class="text-[11px] text-slate-500">نفّذه</p>
                    <p class="text-slate-800">{{ $return->user->name }}</p>
                </div>
                <div>
                    <p class="text-[11px] text-slate-500">الجلسة</p>
                    <p class="nums text-slate-800">#{{ $return->sales_session_id }}</p>
                </div>
                <div>
                    <p class="text-[11px] text-slate-500">التاريخ</p>
                    <p class="nums text-slate-800">{{ $return->created_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>

            <p class="mt-4 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">
                السبب: {{ $return->reason }}
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-bold text-slate-900">الأصناف المرتجعة</h2>
            <table class="w-full text-sm">
                <thead class="text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="pb-2 text-start">المنتج</th>
                        <th class="pb-2 text-start">الوحدة</th>
                        <th class="pb-2 text-center">الكمية</th>
                        <th class="pb-2 text-center">سعر الإرجاع</th>
                        <th class="pb-2 text-center">المبلغ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($return->items as $item)
                        <tr>
                            <td class="py-2 text-slate-800">{{ $item->saleItem->product->name }}</td>
                            <td class="py-2 text-slate-600">{{ $item->saleItem->unit->name }}</td>
                            <td class="nums py-2 text-center text-slate-800">{{ $item->quantity }}</td>
                            <td class="nums py-2 text-center text-slate-600">{{ $item->unit_price }}</td>
                            <td class="nums py-2 text-center font-semibold text-slate-900">{{ $item->line_total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-5 flex items-center justify-between border-t border-slate-200 pt-4">
                <p class="text-sm font-bold text-slate-900">المبلغ المُعاد نقداً</p>
                <p class="nums text-lg font-bold text-red-600">-{{ $return->refund?->amount ?? $return->total_amount }}</p>
            </div>
        </div>

        <p class="text-center text-xs text-slate-400 print:hidden">
            المرتجعات سجل تاريخي ولا يمكن تعديلها أو حذفها. رجعت البضاعة إلى مخزن {{ $return->sale->warehouse->name }}.
        </p>
    </div>

</x-layouts.app>
