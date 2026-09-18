<x-layouts.app title="استلام البضاعة" active="purchases.receipts">

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">رقم الأمر</th>
                        <th class="px-4 py-2.5 text-start font-semibold">المورد</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الإجمالي</th>
                        <th class="px-4 py-2.5 text-center font-semibold">تاريخ التأكيد</th>
                        <th class="px-4 py-2.5 text-center font-semibold">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($purchases as $purchase)
                        <tr class="hover:bg-slate-50">
                            <td class="nums px-4 py-3 font-medium text-brand-700">{{ $purchase->reference_number }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ $purchase->supplier->name }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-700">{{ $purchase->total }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-500">{{ $purchase->confirmed_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('purchases.receive.form', $purchase) }}"
                                   class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">استلام</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">لا توجد أوامر بانتظار الاستلام</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $purchases->links() }}</div>
    </div>

</x-layouts.app>
