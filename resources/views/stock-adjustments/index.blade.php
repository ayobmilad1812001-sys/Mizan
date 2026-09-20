<x-layouts.app title="تسويات المخزون" active="inventory.adjustments">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-4 flex justify-end">
        @can('inventory.adjust')
            <a href="{{ route('stock-adjustments.create') }}"
               class="flex items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <x-icon name="plus" class="size-4" /> تسوية جديدة
            </a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">الرقم</th>
                        <th class="px-4 py-2.5 text-start font-semibold">المخزن</th>
                        <th class="px-4 py-2.5 text-start font-semibold">السبب</th>
                        <th class="px-4 py-2.5 text-start font-semibold">نفّذها</th>
                        <th class="px-4 py-2.5 text-center font-semibold">عدد الأصناف</th>
                        <th class="px-4 py-2.5 text-center font-semibold">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($adjustments as $adjustment)
                        <tr class="cursor-pointer hover:bg-slate-50" onclick="location.href='{{ route('stock-adjustments.show', $adjustment) }}'">
                            <td class="nums px-4 py-3 font-medium text-brand-700">{{ $adjustment->reference_number }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ $adjustment->warehouse->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $adjustment->reason }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $adjustment->user->name }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-700">{{ $adjustment->items_count }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-500">{{ $adjustment->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">لا توجد تسويات بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $adjustments->links() }}</div>
    </div>

</x-layouts.app>
