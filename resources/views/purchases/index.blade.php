<x-layouts.app title="أوامر الشراء" active="purchases.orders">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-4 flex justify-end">
        @can('purchases.create')
            <a href="{{ route('purchases.create') }}"
               class="flex items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <x-icon name="plus" class="size-4" /> أمر شراء جديد
            </a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">الرقم</th>
                        <th class="px-4 py-2.5 text-start font-semibold">المورد</th>
                        <th class="px-4 py-2.5 text-start font-semibold">أنشأه</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الإجمالي</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الحالة</th>
                        <th class="px-4 py-2.5 text-center font-semibold">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($purchases as $purchase)
                        <tr class="cursor-pointer hover:bg-slate-50" onclick="location.href='{{ route('purchases.show', $purchase) }}'">
                            <td class="nums px-4 py-3 font-medium text-brand-700">{{ $purchase->reference_number }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ $purchase->supplier->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $purchase->creator->name }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-700">{{ $purchase->total }}</td>
                            <td class="px-4 py-3 text-center">
                                <span @class([
                                    'rounded px-2 py-1 text-[11px] font-medium',
                                    'bg-slate-100 text-slate-600' => $purchase->status->value === 'draft',
                                    'bg-amber-100 text-amber-700' => $purchase->status->value === 'confirmed',
                                    'bg-emerald-100 text-emerald-700' => $purchase->status->value === 'received',
                                    'bg-red-100 text-red-700' => $purchase->status->value === 'cancelled',
                                ])>{{ $purchase->status->label() }}</span>
                            </td>
                            <td class="nums px-4 py-3 text-center text-slate-500">{{ $purchase->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">لا توجد أوامر شراء بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $purchases->links() }}</div>
    </div>

</x-layouts.app>
