@php
    // بيانات تجريبية للعرض فقط — ستُستبدل ببيانات حقيقية عند بناء الميزات
    $stats = [
        ['label' => 'مبيعات اليوم', 'value' => '18,500.000', 'unit' => 'د.ل', 'delta' => '+12.4%', 'up' => true],
        ['label' => 'عدد الفواتير', 'value' => '24', 'unit' => 'فاتورة', 'delta' => '+3', 'up' => true],
        ['label' => 'قيمة المخزون', 'value' => '412,750.000', 'unit' => 'د.ل', 'delta' => '−2.1%', 'up' => false],
        ['label' => 'أصناف تحتاج طلب', 'value' => '7', 'unit' => 'صنف', 'delta' => '+2', 'up' => false],
    ];

    $lowStock = [
        ['name' => 'آيفون ١٥ أسود ١٢٨ج', 'sku' => 'IP15-BLK-128', 'wh' => 'طرابلس', 'qty' => 0, 'reorder' => 5],
        ['name' => 'سماعات إيربودز برو', 'sku' => 'APP-PRO-2', 'wh' => 'بنغازي', 'qty' => 2, 'reorder' => 10],
        ['name' => 'كرت شاشة RTX 4060', 'sku' => 'RTX-4060-8G', 'wh' => 'طرابلس', 'qty' => 3, 'reorder' => 5],
        ['name' => 'شاحن سريع ٦٥ واط', 'sku' => 'CHG-65W', 'wh' => 'مصراتة', 'qty' => 0, 'reorder' => 20],
        ['name' => 'كيبل تايب سي ٢م', 'sku' => 'CBL-TC-2M', 'wh' => 'طرابلس', 'qty' => 45, 'reorder' => 30],
    ];

    $movements = [
        ['type' => 'PURCHASE_RECEIPT', 'label' => 'استلام شراء', 'ref' => 'PO-1005', 'product' => 'آيفون ١٥ أسود ١٢٨ج', 'wh' => 'طرابلس', 'qty' => 20, 'balance' => 20, 'user' => 'سالم'],
        ['type' => 'SALE', 'label' => 'بيع', 'ref' => 'INV-2026-00542', 'product' => 'آيفون ١٥ أسود ١٢٨ج', 'wh' => 'طرابلس', 'qty' => -2, 'balance' => 18, 'user' => 'أحمد'],
        ['type' => 'ADJUSTMENT', 'label' => 'تسوية', 'ref' => 'ADJ-0012', 'product' => 'آيفون ١٥ أسود ١٢٨ج', 'wh' => 'طرابلس', 'qty' => -1, 'balance' => 17, 'user' => 'سالم'],
        ['type' => 'TRANSFER_OUT', 'label' => 'تحويل صادر', 'ref' => 'TR-0022', 'product' => 'آيفون ١٥ أسود ١٢٨ج', 'wh' => 'طرابلس', 'qty' => -2, 'balance' => 15, 'user' => 'سالم'],
        ['type' => 'RETURN', 'label' => 'إرجاع', 'ref' => 'RET-0012', 'product' => 'سماعات إيربودز برو', 'wh' => 'بنغازي', 'qty' => 1, 'balance' => 2, 'user' => 'أحمد'],
    ];

    $badge = [
        'PURCHASE_RECEIPT' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'SALE' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'ADJUSTMENT' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'TRANSFER_OUT' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'TRANSFER_IN' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'RETURN' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    ];
@endphp

<x-layouts.app title="لوحة التحكم" subtitle="نظرة عامة على النشاط اليومي" active="dashboard">

    {{-- تنبيه البيانات التجريبية --}}
    <div class="mb-5 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
        <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full bg-amber-400 text-[11px] font-bold text-white">!</span>
        <p class="text-sm text-amber-900">
            هذه <strong>واجهة عرض فقط</strong> ببيانات تجريبية ثابتة. لم تُبنَ قاعدة البيانات ولا أي ميزة بعد —
            الهدف مراجعة الشكل والألوان والتنقل قبل كتابة أي كود فعلي.
        </p>
    </div>

    {{-- البطاقات الإحصائية --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $s)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">{{ $s['label'] }}</p>
                <div class="mt-2 flex items-baseline gap-1.5">
                    <span class="nums text-2xl font-bold text-slate-900">{{ $s['value'] }}</span>
                    <span class="text-xs text-slate-500">{{ $s['unit'] }}</span>
                </div>
                <p @class([
                    'mt-2 text-xs font-medium',
                    'text-emerald-600' => $s['up'],
                    'text-red-600' => ! $s['up'],
                ])>
                    <span class="nums">{{ $s['delta'] }}</span>
                    <span class="text-slate-400">مقارنة بالأمس</span>
                </p>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">

        {{-- مخزون منخفض --}}
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm xl:col-span-1">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3.5">
                <h2 class="text-sm font-bold text-slate-900">حالة المخزون</h2>
                <a href="#" class="text-xs font-medium text-brand-700 hover:text-brand-800">عرض الكل</a>
            </div>

            <ul class="divide-y divide-slate-100">
                @foreach ($lowStock as $p)
                    @php
                        if ($p['qty'] == 0) {
                            $rowClass = 'bg-red-50/60';
                            $dotClass = 'bg-red-500';
                            $qtyClass = 'text-red-700';
                            $stateLabel = 'نفد';
                            $stateClass = 'bg-red-100 text-red-700';
                        } elseif ($p['qty'] <= $p['reorder']) {
                            $rowClass = 'bg-amber-50/60';
                            $dotClass = 'bg-amber-500';
                            $qtyClass = 'text-amber-700';
                            $stateLabel = 'قارب الانتهاء';
                            $stateClass = 'bg-amber-100 text-amber-800';
                        } else {
                            $rowClass = '';
                            $dotClass = 'bg-emerald-500';
                            $qtyClass = 'text-slate-700';
                            $stateLabel = 'متوفر';
                            $stateClass = 'bg-emerald-100 text-emerald-700';
                        }
                    @endphp
                    <li class="flex items-center gap-3 px-5 py-3 {{ $rowClass }}">
                        <span class="size-2 shrink-0 rounded-full {{ $dotClass }}"></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $p['name'] }}</p>
                            <p class="mt-0.5 flex items-center gap-1.5 text-[11px] text-slate-500">
                                <span class="nums">{{ $p['sku'] }}</span>
                                <span class="text-slate-300">•</span>
                                <span>{{ $p['wh'] }}</span>
                            </p>
                        </div>
                        <div class="shrink-0 text-end">
                            <p class="nums text-sm font-bold {{ $qtyClass }}">{{ $p['qty'] }}</p>
                            <span class="mt-0.5 inline-block rounded px-1.5 py-0.5 text-[10px] font-medium {{ $stateClass }}">
                                {{ $stateLabel }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- حركات المخزون --}}
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3.5">
                <h2 class="text-sm font-bold text-slate-900">آخر حركات المخزون</h2>
                <a href="#" class="text-xs font-medium text-brand-700 hover:text-brand-800">عرض الكل</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-2.5 text-start font-semibold">النوع</th>
                            <th class="px-4 py-2.5 text-start font-semibold">المرجع</th>
                            <th class="px-4 py-2.5 text-start font-semibold">المنتج</th>
                            <th class="px-4 py-2.5 text-start font-semibold">المخزن</th>
                            <th class="px-4 py-2.5 text-center font-semibold">الكمية</th>
                            <th class="px-4 py-2.5 text-center font-semibold">الرصيد بعدها</th>
                            <th class="px-4 py-2.5 text-start font-semibold">الموظف</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($movements as $m)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="rounded-md px-2 py-1 text-[11px] font-semibold ring-1 ring-inset {{ $badge[$m['type']] }}">
                                        {{ $m['label'] }}
                                    </span>
                                </td>
                                <td class="nums whitespace-nowrap px-4 py-3 text-xs text-slate-600">{{ $m['ref'] }}</td>
                                <td class="px-4 py-3 text-slate-800">{{ $m['product'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $m['wh'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span @class([
                                        'nums font-bold',
                                        'text-emerald-600' => $m['qty'] > 0,
                                        'text-red-600' => $m['qty'] < 0,
                                    ])>{{ $m['qty'] > 0 ? '+' . $m['qty'] : $m['qty'] }}</span>
                                </td>
                                <td class="nums px-4 py-3 text-center font-medium text-slate-700">{{ $m['balance'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $m['user'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ملخص الجلسة --}}
    <div class="mt-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center gap-2">
            <x-icon name="clock" class="size-4 text-slate-400" />
            <h2 class="text-sm font-bold text-slate-900">الجلسة النشطة <span class="nums text-slate-500">#101</span></h2>
            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">مفتوحة</span>
        </div>

        <div class="grid grid-cols-2 gap-px overflow-hidden rounded-lg bg-slate-200 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ([
                ['رصيد افتتاحي', '500.000', 'text-slate-900'],
                ['إجمالي المبيعات', '18,500.000', 'text-slate-900'],
                ['مبيعات نقدية', '12,000.000', 'text-slate-900'],
                ['استرجاعات نقدية', '500.000', 'text-red-600'],
                ['النقد المتوقع', '12,000.000', 'text-brand-700'],
            ] as [$label, $value, $color])
                <div class="bg-white px-4 py-3">
                    <p class="text-[11px] text-slate-500">{{ $label }}</p>
                    <p class="nums mt-1 text-base font-bold {{ $color }}">{{ $value }}</p>
                </div>
            @endforeach
        </div>
    </div>

</x-layouts.app>
