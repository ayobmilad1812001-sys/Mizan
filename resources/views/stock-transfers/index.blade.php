<x-layouts.app title="التحويلات بين المخازن" active="inventory.transfers">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-4 flex justify-end">
        @can('inventory.transfer')
            <a href="{{ route('stock-transfers.create') }}"
               class="flex items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <x-icon name="plus" class="size-4" /> تحويل جديد
            </a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">الرقم</th>
                        <th class="px-4 py-2.5 text-start font-semibold">من مخزن</th>
                        <th class="px-4 py-2.5 text-start font-semibold">إلى مخزن</th>
                        <th class="px-4 py-2.5 text-start font-semibold">نفّذه</th>
                        <th class="px-4 py-2.5 text-center font-semibold">عدد الأصناف</th>
                        <th class="px-4 py-2.5 text-center font-semibold">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transfers as $transfer)
                        <tr class="cursor-pointer hover:bg-slate-50" onclick="location.href='{{ route('stock-transfers.show', $transfer) }}'">
                            <td class="nums px-4 py-3 font-medium text-brand-700">{{ $transfer->reference_number }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ $transfer->fromWarehouse->name }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ $transfer->toWarehouse->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transfer->user->name }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-700">{{ $transfer->items_count }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-500">{{ $transfer->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">لا توجد تحويلات بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $transfers->links() }}</div>
    </div>

</x-layouts.app>
