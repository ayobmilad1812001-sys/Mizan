<x-layouts.app title="المرتجعات" active="sales.returns">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">رقم المرتجع</th>
                        <th class="px-4 py-2.5 text-start font-semibold">الفاتورة الأصلية</th>
                        <th class="px-4 py-2.5 text-start font-semibold">العميل</th>
                        <th class="px-4 py-2.5 text-start font-semibold">السبب</th>
                        <th class="px-4 py-2.5 text-start font-semibold">نفّذه</th>
                        <th class="px-4 py-2.5 text-center font-semibold">المبلغ المُعاد</th>
                        <th class="px-4 py-2.5 text-center font-semibold">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($returns as $return)
                        <tr class="cursor-pointer hover:bg-slate-50" onclick="location.href='{{ route('returns.show', $return) }}'">
                            <td class="nums px-4 py-3 font-medium text-brand-700">{{ $return->reference_number }}</td>
                            <td class="nums px-4 py-3 text-slate-700">{{ $return->sale->invoice_number }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ $return->sale->customer?->name ?? 'زبون نقدي' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $return->reason }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $return->user->name }}</td>
                            <td class="nums px-4 py-3 text-center font-semibold text-red-600">-{{ $return->total_amount }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-500">{{ $return->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">لا توجد مرتجعات بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $returns->links() }}</div>
    </div>

</x-layouts.app>
