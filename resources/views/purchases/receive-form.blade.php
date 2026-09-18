<x-layouts.app title="استلام {{ $purchase->reference_number }}" active="purchases.receipts">

    <div class="mx-auto max-w-2xl space-y-5">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="nums text-sm font-bold text-slate-900">{{ $purchase->reference_number }}</p>
            <p class="text-sm text-slate-500">المورد: {{ $purchase->supplier->name }}</p>

            <table class="mt-4 w-full text-sm">
                <thead class="text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="pb-2 text-start">المنتج</th>
                        <th class="pb-2 text-start">الوحدة</th>
                        <th class="pb-2 text-center">الكمية المطلوبة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($purchase->items as $item)
                        <tr>
                            <td class="py-2">{{ $item->product->name }}</td>
                            <td class="py-2 text-slate-600">{{ $item->unit->name }}</td>
                            <td class="nums py-2 text-center">{{ $item->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-start gap-2 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
                <span>الاستلام يكون كاملاً لكل الأصناف دفعة واحدة (لا استلام جزئي في هذا الإصدار).</span>
            </div>
            <form method="POST" action="{{ route('purchases.receive', $purchase) }}">
                @csrf
                <label class="mb-1 block text-xs font-medium text-slate-600">المخزن المستلِم</label>
                <select name="warehouse_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">اختر المخزن</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
                @error('warehouse_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                <div class="mt-4 flex justify-end gap-3">
                    <a href="{{ route('purchases.show', $purchase) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">إلغاء</a>
                    <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">تأكيد الاستلام</button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
