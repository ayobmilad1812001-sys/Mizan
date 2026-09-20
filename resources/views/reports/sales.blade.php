<x-layouts.app title="تقرير المبيعات" active="reports.sales">

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
            <label class="mb-1 block text-xs font-medium text-slate-600">البائع</label>
            <select name="user_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">الكل</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
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
        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">عرض</button>
        <button type="button" onclick="window.print()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 print:hidden">طباعة</button>
    </form>

    <div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-5">
        @foreach ([
            ['عدد الفواتير', $summary['count'], 'text-slate-900'],
            ['قبل الخصم', $summary['subtotal'], 'text-slate-900'],
            ['الخصم', $summary['discount'], 'text-red-600'],
            ['الضريبة', $summary['tax'], 'text-slate-900'],
            ['الإجمالي', $summary['total'], 'text-brand-700'],
        ] as [$label, $value, $tone])
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] text-slate-500">{{ $label }}</p>
                <p class="nums mt-1 text-lg font-bold {{ $tone }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    @if ($byMethod->isNotEmpty())
        <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-3 text-sm font-bold text-slate-900">حسب طريقة الدفع</h2>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                @foreach ($byMethod as $method => $total)
                    <div class="rounded-lg bg-slate-50 px-4 py-3">
                        <p class="text-xs text-slate-500">{{ App\Enums\PaymentMethod::from($method)->label() }}</p>
                        <p class="nums mt-1 font-bold text-slate-900">{{ $total }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">رقم الفاتورة</th>
                        <th class="px-4 py-2.5 text-start font-semibold">العميل</th>
                        <th class="px-4 py-2.5 text-start font-semibold">البائع</th>
                        <th class="px-4 py-2.5 text-start font-semibold">المخزن</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الدفع</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الخصم</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الإجمالي</th>
                        <th class="px-4 py-2.5 text-center font-semibold">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sales as $sale)
                        <tr class="hover:bg-slate-50">
                            <td class="nums px-4 py-3 font-medium text-brand-700">
                                <a href="{{ route('sales.show', $sale) }}" class="hover:underline">{{ $sale->invoice_number }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-800">{{ $sale->customer?->name ?? 'زبون نقدي' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $sale->user->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $sale->warehouse->name }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $sale->payment_method->label() }}</td>
                            <td class="nums px-4 py-3 text-center text-red-600">{{ $sale->discount_amount }}</td>
                            <td class="nums px-4 py-3 text-center font-semibold text-slate-900">{{ $sale->total }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-500">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">لا توجد مبيعات في هذه الفترة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
