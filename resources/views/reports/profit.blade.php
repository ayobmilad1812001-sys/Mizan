<x-layouts.app title="تقرير الأرباح" active="reports.profit">

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">من</label>
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="nums rounded-lg border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">إلى</label>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="nums rounded-lg border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">عرض</button>
        <button type="button" onclick="window.print()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 print:hidden">طباعة</button>
    </form>

    <div class="mb-5 grid grid-cols-3 gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] text-slate-500">الإيراد (بعد الخصم)</p>
            <p class="nums mt-1 text-lg font-bold text-slate-900">{{ $summary['revenue'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] text-slate-500">التكلفة</p>
            <p class="nums mt-1 text-lg font-bold text-slate-700">{{ $summary['cost'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] text-slate-500">صافي الربح</p>
            <p @class([
                'nums mt-1 text-lg font-bold',
                'text-emerald-700' => bccomp($summary['profit'], '0', 3) >= 0,
                'text-red-600' => bccomp($summary['profit'], '0', 3) < 0,
            ])>{{ $summary['profit'] }}</p>
        </div>
    </div>

    <p class="mb-4 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
        التكلفة محسوبة من سعر الشراء المُجمَّد على كل سطر بيع وقت البيع، فتغيير الأسعار لاحقاً لا يغيّر أرباح الفواتير الصادرة.
        الأرقام لا تطرح المرتجعات بعد.
    </p>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">المنتج</th>
                        <th class="px-4 py-2.5 text-start font-semibold">SKU</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الكمية المباعة</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الإيراد</th>
                        <th class="px-4 py-2.5 text-center font-semibold">التكلفة</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الربح</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الهامش %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rows as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-800">{{ $row->name }}</td>
                            <td class="nums px-4 py-3 text-slate-500">{{ $row->sku }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-700">{{ $row->base_quantity }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-700">{{ $row->revenue }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-600">{{ $row->cost }}</td>
                            <td @class([
                                'nums px-4 py-3 text-center font-semibold',
                                'text-emerald-700' => bccomp($row->profit, '0', 3) >= 0,
                                'text-red-600' => bccomp($row->profit, '0', 3) < 0,
                            ])>{{ $row->profit }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-600">{{ $row->margin }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">لا توجد مبيعات في هذه الفترة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
