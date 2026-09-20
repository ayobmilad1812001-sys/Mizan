<x-layouts.app title="نتائج البحث" active="dashboard">

    <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('search') }}" class="flex gap-2">
            <input type="search" name="q" value="{{ $term }}" autofocus placeholder="رقم فاتورة، اسم منتج، SKU، اسم عميل…"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            <button type="submit" class="shrink-0 rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">بحث</button>
        </form>
        @if ($term !== '')
            <p class="mt-2 text-xs text-slate-500">
                نتائج البحث عن «{{ $term }}» — النتائج مقيّدة بصلاحياتك.
            </p>
        @endif
    </div>

    @php $empty = $products->isEmpty() && $sales->isEmpty() && $purchases->isEmpty() && $customers->isEmpty(); @endphp

    @if ($term === '')
        <p class="rounded-xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-400 shadow-sm">اكتب ما تبحث عنه أعلاه.</p>
    @elseif ($empty)
        <p class="rounded-xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-400 shadow-sm">لا توجد نتائج مطابقة.</p>
    @else
        <div class="space-y-5">
            @if ($sales->isNotEmpty())
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-3 text-sm font-bold text-slate-900">الفواتير</h2>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($sales as $sale)
                                <tr class="cursor-pointer hover:bg-slate-50" onclick="location.href='{{ route('sales.show', $sale) }}'">
                                    <td class="nums py-2 font-medium text-brand-700">{{ $sale->invoice_number }}</td>
                                    <td class="py-2 text-slate-700">{{ $sale->customer?->name ?? 'زبون نقدي' }}</td>
                                    <td class="py-2 text-xs text-slate-500">{{ $sale->user->name }}</td>
                                    <td class="nums py-2 text-center font-semibold text-slate-900">{{ $sale->total }}</td>
                                    <td class="nums py-2 text-center text-xs text-slate-500">{{ $sale->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($products->isNotEmpty())
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-3 text-sm font-bold text-slate-900">المنتجات</h2>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($products as $product)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-2 text-slate-800">{{ $product->name }}</td>
                                    <td class="nums py-2 text-slate-500">{{ $product->sku }}</td>
                                    <td class="py-2 text-center text-xs text-slate-500">{{ $product->baseUnit?->name }}</td>
                                    <td class="nums py-2 text-center text-slate-700">{{ $product->baseUnit?->selling_price }}</td>
                                    <td class="py-2 text-center">
                                        @can('products.view')
                                            <a href="{{ route('products.index', ['search' => $product->sku]) }}" class="text-xs font-semibold text-brand-700 hover:underline">عرض</a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($purchases->isNotEmpty())
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-3 text-sm font-bold text-slate-900">أوامر الشراء</h2>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($purchases as $purchase)
                                <tr class="cursor-pointer hover:bg-slate-50" onclick="location.href='{{ route('purchases.show', $purchase) }}'">
                                    <td class="nums py-2 font-medium text-brand-700">{{ $purchase->reference_number }}</td>
                                    <td class="py-2 text-slate-700">{{ $purchase->supplier->name }}</td>
                                    <td class="py-2 text-center text-xs text-slate-500">{{ $purchase->status->label() }}</td>
                                    <td class="nums py-2 text-center font-semibold text-slate-900">{{ $purchase->total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($customers->isNotEmpty())
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-3 text-sm font-bold text-slate-900">العملاء</h2>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($customers as $customer)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-2 text-slate-800">{{ $customer->name }}</td>
                                    <td class="nums py-2 text-slate-500" dir="ltr">{{ $customer->phone ?? '—' }}</td>
                                    <td class="py-2 text-center text-xs text-slate-500">{{ $customer->status->label() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

</x-layouts.app>
