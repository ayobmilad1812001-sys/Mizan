<x-layouts.app title="أرصدة المخازن" active="inventory.stocks">

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
                        <th class="px-4 py-2.5 text-start font-semibold">المنتج</th>
                        <th class="px-4 py-2.5 text-start font-semibold">SKU</th>
                        <th class="px-4 py-2.5 text-start font-semibold">المخزن</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الرصيد</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($stocks as $stock)
                        @php
                            $level = $stock->level();
                            $rowClass = match ($level->value) {
                                'out' => 'bg-red-50/60',
                                'low' => 'bg-amber-50/60',
                                default => '',
                            };
                            $badgeClass = match ($level->value) {
                                'out' => 'bg-red-100 text-red-700',
                                'low' => 'bg-amber-100 text-amber-800',
                                default => 'bg-emerald-100 text-emerald-700',
                            };
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $stock->product->name }}</td>
                            <td class="nums px-4 py-3 text-slate-600">{{ $stock->product->sku }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $stock->warehouse->name }}</td>
                            <td class="nums px-4 py-3 text-center font-bold text-slate-800">{{ $stock->quantity }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded px-2 py-1 text-[11px] font-medium {{ $badgeClass }}">{{ $level->label() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">لا يوجد مخزون مسجَّل بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $stocks->links() }}</div>
    </div>

</x-layouts.app>
