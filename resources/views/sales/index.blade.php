<x-layouts.app title="الفواتير" active="sales.invoices">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @can('sales.create')
        <div class="mb-4 flex justify-end">
            <a href="{{ route('sales.pos') }}"
               class="flex items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <x-icon name="plus" class="size-4" /> فاتورة جديدة
            </a>
        </div>
    @endcan

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">رقم الفاتورة</th>
                        <th class="px-4 py-2.5 text-start font-semibold">العميل</th>
                        <th class="px-4 py-2.5 text-start font-semibold">البائع</th>
                        <th class="px-4 py-2.5 text-start font-semibold">المخزن</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الدفع</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الإجمالي</th>
                        <th class="px-4 py-2.5 text-center font-semibold">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sales as $sale)
                        <tr class="cursor-pointer hover:bg-slate-50" onclick="location.href='{{ route('sales.show', $sale) }}'">
                            <td class="nums px-4 py-3 font-medium text-brand-700">{{ $sale->invoice_number }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ $sale->customer?->name ?? 'زبون نقدي' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $sale->user->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $sale->warehouse->name }}</td>
                            <td class="px-4 py-3 text-center">
                                <span @class([
                                    'rounded px-2 py-1 text-[11px] font-medium',
                                    'bg-emerald-100 text-emerald-700' => $sale->payment_method->value === 'cash',
                                    'bg-slate-100 text-slate-600' => $sale->payment_method->value !== 'cash',
                                ])>{{ $sale->payment_method->label() }}</span>
                            </td>
                            <td class="nums px-4 py-3 text-center font-semibold text-slate-900">{{ $sale->total }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-500">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">لا توجد فواتير بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $sales->links() }}</div>
    </div>

</x-layouts.app>
