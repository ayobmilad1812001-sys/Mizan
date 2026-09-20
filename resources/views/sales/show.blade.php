<x-layouts.app title="الفاتورة {{ $sale->invoice_number }}" active="sales.invoices">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800 print:hidden">
            {{ session('status') }}
        </div>
    @endif

    <div class="mx-auto max-w-3xl space-y-5">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="nums text-lg font-bold text-slate-900">{{ $sale->invoice_number }}</p>
                    <p class="text-sm text-slate-500">{{ $sale->customer?->name ?? 'زبون نقدي' }}</p>
                </div>
                <div class="flex items-center gap-2 print:hidden">
                    <span @class([
                        'rounded-full px-3 py-1 text-xs font-semibold',
                        'bg-emerald-100 text-emerald-700' => $sale->payment_method->value === 'cash',
                        'bg-slate-100 text-slate-600' => $sale->payment_method->value !== 'cash',
                    ])>{{ $sale->payment_method->label() }}</span>
                    @can('returns.create')
                        @if ($sale->isReturnable() && $sale->items->contains(fn ($item) => bccomp($item->returnableQuantity(), '0', 3) > 0))
                            <a href="{{ route('returns.create', $sale) }}"
                               class="rounded-lg border border-amber-300 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-50">
                                إرجاع
                            </a>
                        @endif
                    @endcan
                    <button type="button" onclick="window.print()"
                            class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        طباعة
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                <div>
                    <p class="text-[11px] text-slate-500">البائع</p>
                    <p class="text-slate-800">{{ $sale->user->name }}</p>
                </div>
                <div>
                    <p class="text-[11px] text-slate-500">المخزن</p>
                    <p class="text-slate-800">{{ $sale->warehouse->name }}</p>
                </div>
                <div>
                    <p class="text-[11px] text-slate-500">الجلسة</p>
                    <p class="nums text-slate-800">#{{ $sale->sales_session_id }}</p>
                </div>
                <div>
                    <p class="text-[11px] text-slate-500">التاريخ</p>
                    <p class="nums text-slate-800">{{ $sale->created_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>

            @if ($sale->notes)
                <p class="mt-4 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">{{ $sale->notes }}</p>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-bold text-slate-900">الأصناف</h2>
            <table class="w-full text-sm">
                <thead class="text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="pb-2 text-start">المنتج</th>
                        <th class="pb-2 text-start">الوحدة</th>
                        <th class="pb-2 text-center">الكمية</th>
                        <th class="pb-2 text-center">السعر</th>
                        <th class="pb-2 text-center">الخصم</th>
                        <th class="pb-2 text-center">الإجمالي</th>
                        @if ($sale->items->contains(fn ($i) => bccomp($i->returned_quantity, '0', 3) > 0))
                            <th class="pb-2 text-center">أُرجع</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($sale->items as $item)
                        <tr>
                            <td class="py-2 text-slate-800">{{ $item->product->name }}</td>
                            <td class="py-2 text-slate-600">{{ $item->unit->name }}</td>
                            <td class="nums py-2 text-center text-slate-800">{{ $item->quantity }}</td>
                            <td class="nums py-2 text-center text-slate-600">{{ $item->unit_price }}</td>
                            <td class="nums py-2 text-center text-red-600">{{ $item->discount_share }}</td>
                            <td class="nums py-2 text-center font-semibold text-slate-900">{{ $item->line_total }}</td>
                            @if ($sale->items->contains(fn ($i) => bccomp($i->returned_quantity, '0', 3) > 0))
                                <td class="nums py-2 text-center text-amber-700">{{ bccomp($item->returned_quantity, '0', 3) > 0 ? $item->returned_quantity : '—' }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <dl class="mt-5 space-y-2 border-t border-slate-200 pt-4 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">الإجمالي قبل الخصم</dt>
                    <dd class="nums text-slate-800">{{ $sale->subtotal }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">الخصم (<span class="nums">{{ $sale->discount_percent }}</span>%)</dt>
                    <dd class="nums text-red-600">-{{ $sale->discount_amount }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">الضريبة (<span class="nums">{{ $sale->tax_rate }}</span>%)</dt>
                    <dd class="nums text-slate-800">{{ $sale->tax_amount }}</dd>
                </div>
                <div class="flex justify-between border-t border-slate-200 pt-2">
                    <dt class="font-bold text-slate-900">الإجمالي</dt>
                    <dd class="nums text-lg font-bold text-brand-700">{{ $sale->total }}</dd>
                </div>
            </dl>
        </div>

        @if ($sale->returns->isNotEmpty())
            <div class="rounded-xl border border-amber-200 bg-amber-50/40 p-5 shadow-sm print:hidden">
                <h2 class="mb-4 text-sm font-bold text-slate-900">مرتجعات هذه الفاتورة</h2>
                <table class="w-full text-sm">
                    <thead class="text-[11px] uppercase text-slate-500">
                        <tr>
                            <th class="pb-2 text-start">رقم المرتجع</th>
                            <th class="pb-2 text-start">السبب</th>
                            <th class="pb-2 text-start">نفّذه</th>
                            <th class="pb-2 text-center">المبلغ المُعاد</th>
                            <th class="pb-2 text-center">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100">
                        @foreach ($sale->returns as $return)
                            <tr class="cursor-pointer hover:bg-amber-50" onclick="location.href='{{ route('returns.show', $return) }}'">
                                <td class="nums py-2 font-medium text-brand-700">{{ $return->reference_number }}</td>
                                <td class="py-2 text-slate-700">{{ $return->reason }}</td>
                                <td class="py-2 text-slate-600">{{ $return->user->name }}</td>
                                <td class="nums py-2 text-center font-semibold text-red-600">-{{ $return->total_amount }}</td>
                                <td class="nums py-2 text-center text-slate-500">{{ $return->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <p class="text-center text-xs text-slate-400 print:hidden">
            الفواتير سجل تاريخي ولا يمكن تعديلها أو حذفها.
            @unless ($sale->isReturnable())
                هذه الفاتورة غير نقدية، فلا تقبل إرجاعاً.
            @endunless
        </p>
    </div>

</x-layouts.app>
