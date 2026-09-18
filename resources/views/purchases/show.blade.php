<x-layouts.app title="أمر الشراء {{ $purchase->reference_number }}" active="purchases.orders">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mx-auto max-w-3xl space-y-5">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="nums text-lg font-bold text-slate-900">{{ $purchase->reference_number }}</p>
                    <p class="text-sm text-slate-500">المورد: {{ $purchase->supplier->name }}</p>
                </div>
                <span @class([
                    'rounded-full px-3 py-1 text-xs font-semibold',
                    'bg-slate-100 text-slate-600' => $purchase->status->value === 'draft',
                    'bg-amber-100 text-amber-700' => $purchase->status->value === 'confirmed',
                    'bg-emerald-100 text-emerald-700' => $purchase->status->value === 'received',
                    'bg-red-100 text-red-700' => $purchase->status->value === 'cancelled',
                ])>{{ $purchase->status->label() }}</span>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                <div>
                    <p class="text-[11px] text-slate-500">أنشأه</p>
                    <p class="text-slate-800">{{ $purchase->creator->name }}</p>
                </div>
                @if ($purchase->confirmer)
                    <div>
                        <p class="text-[11px] text-slate-500">أكّده</p>
                        <p class="text-slate-800">{{ $purchase->confirmer->name }}</p>
                    </div>
                @endif
                @if ($purchase->canceller)
                    <div>
                        <p class="text-[11px] text-slate-500">ألغاه</p>
                        <p class="text-slate-800">{{ $purchase->canceller->name }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-[11px] text-slate-500">الإجمالي</p>
                    <p class="nums font-bold text-slate-900">{{ $purchase->total }}</p>
                </div>
            </div>

            @if ($purchase->notes)
                <p class="mt-4 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">{{ $purchase->notes }}</p>
            @endif

            <div class="mt-4 flex flex-wrap gap-2">
                @can('purchases.confirm')
                    @if ($purchase->isDraft())
                        <form method="POST" action="{{ route('purchases.confirm', $purchase) }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">تأكيد الأمر</button>
                        </form>
                    @endif
                    @if (in_array($purchase->status->value, ['draft', 'confirmed'], true))
                        <form method="POST" action="{{ route('purchases.cancel', $purchase) }}">
                            @csrf
                            <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">إلغاء الأمر</button>
                        </form>
                    @endif
                @endcan
                @can('purchases.receive')
                    @if ($purchase->canBeReceived())
                        <a href="{{ route('purchases.receive.form', $purchase) }}" class="rounded-lg border border-brand-300 px-4 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-50">استلام البضاعة</a>
                    @endif
                @endcan
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-bold text-slate-900">الأصناف</h2>
            <table class="w-full text-sm">
                <thead class="text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="pb-2 text-start">المنتج</th>
                        <th class="pb-2 text-start">الوحدة</th>
                        <th class="pb-2 text-center">الكمية</th>
                        <th class="pb-2 text-center">تكلفة الوحدة</th>
                        <th class="pb-2 text-center">الإجمالي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($purchase->items as $item)
                        <tr>
                            <td class="py-2">{{ $item->product->name }}</td>
                            <td class="py-2 text-slate-600">{{ $item->unit->name }}</td>
                            <td class="nums py-2 text-center">{{ $item->quantity }}</td>
                            <td class="nums py-2 text-center">{{ $item->unit_cost }}</td>
                            <td class="nums py-2 text-center font-medium">{{ $item->line_total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($purchase->receipt)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-2 text-sm font-bold text-slate-900">الاستلام</h2>
                <p class="text-sm text-slate-600">
                    استُلمت في مخزن <strong>{{ $purchase->receipt->warehouse->name }}</strong>
                    بواسطة {{ $purchase->receipt->receiver->name }}
                    بتاريخ <span class="nums">{{ $purchase->receipt->received_at->format('Y-m-d H:i') }}</span>
                </p>
            </div>
        @endif
    </div>

</x-layouts.app>
