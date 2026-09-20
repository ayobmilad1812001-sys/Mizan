<x-layouts.app title="تقرير حركات المخزون" active="reports.movements">

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">من</label>
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="nums rounded-lg border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">إلى</label>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="nums rounded-lg border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">المخزن</label>
            <select name="warehouse_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">الكل</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">النوع</label>
            <select name="movement_type" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">الكل</option>
                @foreach (App\Enums\StockMovementType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(request('movement_type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">عرض</button>
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
                        <th class="px-4 py-2.5 text-start font-semibold">المرجع</th>
                        <th class="px-4 py-2.5 text-center font-semibold">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($movements as $movement)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <span class="rounded bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-700">{{ $movement->movement_type->label() }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-800">{{ $movement->product->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $movement->warehouse->name }}</td>
                            <td @class([
                                'nums px-4 py-3 text-center font-semibold',
                                'text-emerald-600' => bccomp($movement->quantity, '0', 3) > 0,
                                'text-red-600' => bccomp($movement->quantity, '0', 3) < 0,
                            ])>{{ bccomp($movement->quantity, '0', 3) > 0 ? '+' : '' }}{{ $movement->quantity }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-700">{{ $movement->balance_after }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $movement->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">
                                <span dir="ltr">{{ $movement->reference_type }}</span><span class="nums"> #{{ $movement->reference_id }}</span>
                            </td>
                            <td class="nums px-4 py-3 text-center text-slate-500">{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">لا توجد حركات في هذه الفترة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $movements->links() }}</div>
    </div>

</x-layouts.app>
