<x-layouts.app title="لوحة التحكم" active="dashboard">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h1 class="text-base font-bold text-slate-900">أهلاً {{ auth()->user()->name }}</h1>
        <p class="mt-1 text-sm text-slate-500">
            {{ auth()->user()->role->name }}
            @if ($session)
                — جلسة بيع مفتوحة رقم <a href="{{ route('sales-sessions.show', $session) }}" class="nums font-semibold text-brand-700 hover:underline">#{{ $session->id }}</a>
            @elseif (auth()->user()->hasPermission('sessions.start'))
                — لا توجد جلسة مفتوحة.
                <a href="{{ route('sales.pos') }}" class="font-semibold text-brand-700 hover:underline">افتح جلسة للبدء</a>
            @endif
        </p>
    </div>

    @if (count($cards))
        <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($cards as $card)
                <div @class([
                    'rounded-xl border bg-white p-5 shadow-sm',
                    'border-amber-200' => ($card['tone'] ?? null) === 'warn',
                    'border-slate-200' => ($card['tone'] ?? null) !== 'warn',
                ])>
                    <p class="text-xs text-slate-500">{{ $card['label'] }}</p>
                    <p @class([
                        'nums mt-2 text-2xl font-bold',
                        'text-amber-600' => ($card['tone'] ?? null) === 'warn',
                        'text-slate-900' => ($card['tone'] ?? null) !== 'warn',
                    ])>{{ $card['value'] }}</p>
                    <p class="mt-1 text-[11px] text-slate-400">{{ $card['sub'] }}</p>
                </div>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

        @if ($lowStock->isNotEmpty())
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900">أصناف تحتاج انتباهاً</h2>
                    <a href="{{ route('warehouse-stocks.index') }}" class="text-xs font-semibold text-brand-700 hover:underline">كل الأرصدة</a>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($lowStock as $stock)
                            @php $level = $stock->level(); @endphp
                            <tr>
                                <td class="py-2 text-slate-800">{{ $stock->product->name }}</td>
                                <td class="py-2 text-xs text-slate-500">{{ $stock->warehouse->name }}</td>
                                <td class="nums py-2 text-center font-semibold text-slate-900">{{ $stock->quantity }}</td>
                                <td class="py-2 text-center">
                                    <span @class([
                                        'rounded px-2 py-1 text-[11px] font-medium',
                                        'bg-red-100 text-red-700' => $level->value === 'out',
                                        'bg-amber-100 text-amber-700' => $level->value === 'low',
                                    ])>{{ $level->label() }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($recentSales->isNotEmpty())
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900">أحدث الفواتير</h2>
                    <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-brand-700 hover:underline">كل الفواتير</a>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recentSales as $sale)
                            <tr class="cursor-pointer hover:bg-slate-50" onclick="location.href='{{ route('sales.show', $sale) }}'">
                                <td class="nums py-2 font-medium text-brand-700">{{ $sale->invoice_number }}</td>
                                <td class="py-2 text-slate-700">{{ $sale->customer?->name ?? 'زبون نقدي' }}</td>
                                <td class="nums py-2 text-center font-semibold text-slate-900">{{ $sale->total }}</td>
                                <td class="nums py-2 text-center text-xs text-slate-500">{{ $sale->created_at->format('m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($recentMovements->isNotEmpty())
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900">أحدث حركات المخزون</h2>
                    <a href="{{ route('stock-movements.index') }}" class="text-xs font-semibold text-brand-700 hover:underline">كل الحركات</a>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recentMovements as $movement)
                            <tr>
                                <td class="py-2">
                                    <span class="rounded bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-700">{{ $movement->movement_type->label() }}</span>
                                </td>
                                <td class="py-2 text-slate-800">{{ $movement->product->name }}</td>
                                <td class="py-2 text-xs text-slate-500">{{ $movement->warehouse->name }}</td>
                                <td @class([
                                    'nums py-2 text-center font-semibold',
                                    'text-emerald-600' => bccomp($movement->quantity, '0', 3) > 0,
                                    'text-red-600' => bccomp($movement->quantity, '0', 3) < 0,
                                ])>{{ bccomp($movement->quantity, '0', 3) > 0 ? '+' : '' }}{{ $movement->quantity }}</td>
                                <td class="py-2 text-xs text-slate-500">{{ $movement->user?->name ?? '—' }}</td>
                                <td class="nums py-2 text-center text-xs text-slate-500">{{ $movement->created_at->format('m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</x-layouts.app>
