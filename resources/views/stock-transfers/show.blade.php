<x-layouts.app title="التحويل {{ $transfer->reference_number }}" active="inventory.transfers">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mx-auto max-w-3xl space-y-5">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="nums text-lg font-bold text-slate-900">{{ $transfer->reference_number }}</p>
            <p class="mt-1 text-sm text-slate-600">
                <span class="text-slate-800">{{ $transfer->fromWarehouse->name }}</span>
                <span class="mx-2 text-slate-400">←</span>
                <span class="text-slate-800">{{ $transfer->toWarehouse->name }}</span>
            </p>

            <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-[11px] text-slate-500">نفّذه</p>
                    <p class="text-slate-800">{{ $transfer->user->name }}</p>
                </div>
                <div>
                    <p class="text-[11px] text-slate-500">التاريخ</p>
                    <p class="nums text-slate-800">{{ $transfer->created_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>

            @if ($transfer->notes)
                <p class="mt-4 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">{{ $transfer->notes }}</p>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-bold text-slate-900">الأصناف المحوَّلة</h2>
            <table class="w-full text-sm">
                <thead class="text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="pb-2 text-start">المنتج</th>
                        <th class="pb-2 text-start">الوحدة</th>
                        <th class="pb-2 text-center">الكمية</th>
                        <th class="pb-2 text-center">بالوحدة الأساسية</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($transfer->items as $item)
                        <tr>
                            <td class="py-2 text-slate-800">{{ $item->product->name }}</td>
                            <td class="py-2 text-slate-600">{{ $item->unit->name }}</td>
                            <td class="nums py-2 text-center text-slate-800">{{ $item->quantity }}</td>
                            <td class="nums py-2 text-center text-slate-600">{{ $item->base_quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="text-center text-xs text-slate-400">التحويلات سجل تاريخي ولا يمكن تعديلها أو حذفها.</p>
    </div>

</x-layouts.app>
