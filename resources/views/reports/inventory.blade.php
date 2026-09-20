<x-layouts.app title="تقرير المخزون" active="reports.inventory">

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">المخزن</label>
            <select name="warehouse_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">كل المخازن</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">الحالة</label>
            <select name="level" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">الكل</option>
                @foreach (App\Enums\StockLevel::cases() as $level)
                    <option value="{{ $level->value }}" @selected(request('level') === $level->value)>{{ $level->label() }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">عرض</button>
        <button type="button" onclick="window.print()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 print:hidden">طباعة</button>
    </form>

    <div class="mb-5 grid grid-cols-3 gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] text-slate-500">عدد السطور</p>
            <p class="nums mt-1 text-lg font-bold text-slate-900">{{ $summary['lines'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] text-slate-500">إجمالي الوحدات</p>
            <p class="nums mt-1 text-lg font-bold text-slate-900">{{ $summary['units'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] text-slate-500">قيمة المخزون بالتكلفة</p>
            <p class="nums mt-1 text-lg font-bold text-brand-700">{{ $summary['value'] }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">المنتج</th>
                        <th class="px-4 py-2.5 text-start font-semibold">SKU</th>
                        <th class="px-4 py-2.5 text-start font-semibold">المخزن</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الرصيد</th>
                        <th class="px-4 py-2.5 text-center font-semibold">حد إعادة الطلب</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الحالة</th>
                        <th class="px-4 py-2.5 text-center font-semibold">القيمة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($stocks as $stock)
                        @php $level = $stock->level(); @endphp
                        <tr @class([
                            'hover:bg-slate-50',
                            'bg-red-50/50' => $level->value === 'out',
                            'bg-amber-50/50' => $level->value === 'low',
                        ])>
                            <td class="px-4 py-3 text-slate-800">{{ $stock->product->name }}</td>
                            <td class="nums px-4 py-3 text-slate-500">{{ $stock->product->sku }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $stock->warehouse->name }}</td>
                            <td class="nums px-4 py-3 text-center font-semibold text-slate-900">{{ $stock->quantity }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-500">{{ $stock->product->reorder_level }}</td>
                            <td class="px-4 py-3 text-center">
                                <span @class([
                                    'rounded px-2 py-1 text-[11px] font-medium',
                                    'bg-red-100 text-red-700' => $level->value === 'out',
                                    'bg-amber-100 text-amber-700' => $level->value === 'low',
                                    'bg-emerald-100 text-emerald-700' => $level->value === 'available',
                                ])>{{ $level->label() }}</span>
                            </td>
                            <td class="nums px-4 py-3 text-center text-slate-700">
                                {{ bcmul($stock->quantity, (string) ($stock->product->baseUnit?->purchase_price ?? '0'), 3) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">لا توجد أرصدة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
