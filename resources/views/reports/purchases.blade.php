<x-layouts.app title="تقرير المشتريات" active="reports.purchases">

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
            <label class="mb-1 block text-xs font-medium text-slate-600">الحالة</label>
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">الكل</option>
                @foreach (App\Enums\PurchaseStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">عرض</button>
        <button type="button" onclick="window.print()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 print:hidden">طباعة</button>
    </form>

    <div class="mb-5 grid grid-cols-3 gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] text-slate-500">عدد الأوامر</p>
            <p class="nums mt-1 text-lg font-bold text-slate-900">{{ $summary['count'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] text-slate-500">المستلَمة</p>
            <p class="nums mt-1 text-lg font-bold text-emerald-700">{{ $summary['received'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] text-slate-500">إجمالي القيمة</p>
            <p class="nums mt-1 text-lg font-bold text-brand-700">{{ $summary['total'] }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">الرقم</th>
                        <th class="px-4 py-2.5 text-start font-semibold">المورد</th>
                        <th class="px-4 py-2.5 text-start font-semibold">أنشأه</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الحالة</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الإجمالي</th>
                        <th class="px-4 py-2.5 text-center font-semibold">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($purchases as $purchase)
                        <tr class="hover:bg-slate-50">
                            <td class="nums px-4 py-3 font-medium text-brand-700">
                                <a href="{{ route('purchases.show', $purchase) }}" class="hover:underline">{{ $purchase->reference_number }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-800">{{ $purchase->supplier->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $purchase->creator->name }}</td>
                            <td class="px-4 py-3 text-center">
                                <span @class([
                                    'rounded px-2 py-1 text-[11px] font-medium',
                                    'bg-slate-100 text-slate-600' => $purchase->status->value === 'draft',
                                    'bg-amber-100 text-amber-700' => $purchase->status->value === 'confirmed',
                                    'bg-emerald-100 text-emerald-700' => $purchase->status->value === 'received',
                                    'bg-red-100 text-red-700' => $purchase->status->value === 'cancelled',
                                ])>{{ $purchase->status->label() }}</span>
                            </td>
                            <td class="nums px-4 py-3 text-center font-semibold text-slate-900">{{ $purchase->total }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-500">{{ $purchase->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">لا توجد مشتريات في هذه الفترة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
