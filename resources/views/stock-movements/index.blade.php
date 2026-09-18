<x-layouts.app title="حركات المخزون" active="inventory.movements">

    @php
        $badge = [
            'purchase_receipt' => 'bg-blue-50 text-blue-700 ring-blue-200',
            'sale' => 'bg-violet-50 text-violet-700 ring-violet-200',
            'adjustment' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'transfer_out' => 'bg-slate-100 text-slate-700 ring-slate-200',
            'transfer_in' => 'bg-slate-100 text-slate-700 ring-slate-200',
            'return' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        ];
    @endphp

    <form method="GET" class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-48">
            <x-icon name="search" class="pointer-events-none absolute inset-y-0 start-3 my-auto size-4 text-slate-400" />
            <input type="search" name="search" value="{{ request('search') }}" placeholder="ابحث بالاسم أو SKU"
                   class="w-full rounded-lg border border-slate-300 bg-white py-2 pe-3 ps-9 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" />
        </div>
        <select name="warehouse_id" class="rounded-lg border border-slate-300 py-2 px-3 text-sm" onchange="this.form.submit()">
            <option value="">كل المخازن</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">
            <x-icon name="filter" class="size-4" />
        </button>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">النوع</th>
                        <th class="px-4 py-2.5 text-start font-semibold">المنتج</th>
                        <th class="px-4 py-2.5 text-start font-semibold">المخزن</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الكمية</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الرصيد بعدها</th>
                        <th class="px-4 py-2.5 text-start font-semibold">الموظف</th>
                        <th class="px-4 py-2.5 text-center font-semibold">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($movements as $movement)
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="rounded-md px-2 py-1 text-[11px] font-semibold ring-1 ring-inset {{ $badge[$movement->movement_type->value] }}">
                                    {{ $movement->movement_type->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-800">{{ $movement->product->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $movement->warehouse->name }}</td>
                            <td class="px-4 py-3 text-center">
                                <span @class([
                                    'nums font-bold',
                                    'text-emerald-600' => $movement->quantity > 0,
                                    'text-red-600' => $movement->quantity < 0,
                                ])>{{ $movement->quantity > 0 ? '+'.$movement->quantity : $movement->quantity }}</span>
                            </td>
                            <td class="nums px-4 py-3 text-center font-medium text-slate-700">{{ $movement->balance_after }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $movement->user->name }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-500">{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">لا توجد حركات مخزون بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $movements->links() }}</div>
    </div>

</x-layouts.app>
