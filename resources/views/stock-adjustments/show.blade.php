<x-layouts.app title="التسوية {{ $adjustment->reference_number }}" active="inventory.adjustments">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mx-auto max-w-3xl space-y-5">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="nums text-lg font-bold text-slate-900">{{ $adjustment->reference_number }}</p>
            <p class="text-sm text-slate-500">المخزن: {{ $adjustment->warehouse->name }}</p>

            <div class="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                <div>
                    <p class="text-[11px] text-slate-500">نفّذها</p>
                    <p class="text-slate-800">{{ $adjustment->user->name }}</p>
                </div>
                <div>
                    <p class="text-[11px] text-slate-500">التاريخ</p>
                    <p class="nums text-slate-800">{{ $adjustment->created_at->format('Y-m-d H:i') }}</p>
                </div>
                <div>
                    <p class="text-[11px] text-slate-500">السبب</p>
                    <p class="text-slate-800">{{ $adjustment->reason }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-bold text-slate-900">الأصناف المسوّاة</h2>
            <table class="w-full text-sm">
                <thead class="text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="pb-2 text-start">المنتج</th>
                        <th class="pb-2 text-center">الرصيد قبل</th>
                        <th class="pb-2 text-center">الرصيد بعد</th>
                        <th class="pb-2 text-center">الفرق</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($adjustment->items as $item)
                        <tr>
                            <td class="py-2 text-slate-800">{{ $item->product->name }}</td>
                            <td class="nums py-2 text-center text-slate-600">{{ $item->previous_quantity }}</td>
                            <td class="nums py-2 text-center text-slate-800">{{ $item->new_quantity }}</td>
                            <td @class([
                                'nums py-2 text-center font-semibold',
                                'text-emerald-600' => bccomp($item->difference, '0', 3) > 0,
                                'text-red-600' => bccomp($item->difference, '0', 3) < 0,
                            ])>{{ bccomp($item->difference, '0', 3) > 0 ? '+' : '' }}{{ $item->difference }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="text-center text-xs text-slate-400">التسويات سجل تاريخي ولا يمكن تعديلها أو حذفها.</p>
    </div>

</x-layouts.app>
