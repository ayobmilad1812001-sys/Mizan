@php
    $nav = [
        [
            'type' => 'link',
            'label' => 'لوحة التحكم',
            'icon' => 'dashboard',
            'key' => 'dashboard',
            'url' => route('dashboard'),
        ],
        [
            'type' => 'group',
            'label' => 'المبيعات',
            'icon' => 'sales',
            'key' => 'sales',
            'items' => [
                ['label' => 'نقطة البيع', 'key' => 'sales.pos', 'url' => '#'],
                ['label' => 'الفواتير', 'key' => 'sales.invoices', 'url' => '#'],
                ['label' => 'المرتجعات', 'key' => 'sales.returns', 'url' => '#'],
                ['label' => 'جلسات البيع', 'key' => 'sales.sessions', 'url' => '#'],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'المخزون',
            'icon' => 'inventory',
            'key' => 'inventory',
            'items' => [
                ['label' => 'أرصدة المخازن', 'key' => 'inventory.stocks', 'url' => '#'],
                ['label' => 'حركات المخزون', 'key' => 'inventory.movements', 'url' => '#'],
                ['label' => 'تسويات المخزون', 'key' => 'inventory.adjustments', 'url' => '#'],
                ['label' => 'التحويلات بين المخازن', 'key' => 'inventory.transfers', 'url' => '#'],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'المشتريات',
            'icon' => 'purchases',
            'key' => 'purchases',
            'items' => [
                ['label' => 'أوامر الشراء', 'key' => 'purchases.orders', 'url' => '#'],
                ['label' => 'استلام البضاعة', 'key' => 'purchases.receipts', 'url' => '#'],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'التعريفات',
            'icon' => 'catalog',
            'key' => 'catalog',
            'items' => [
                ['label' => 'المنتجات', 'key' => 'catalog.products', 'url' => '#'],
                ['label' => 'التصنيفات', 'key' => 'catalog.categories', 'url' => '#'],
                ['label' => 'الماركات', 'key' => 'catalog.brands', 'url' => '#'],
                ['label' => 'المخازن', 'key' => 'catalog.warehouses', 'url' => '#'],
                ['label' => 'العملاء', 'key' => 'catalog.customers', 'url' => '#'],
                ['label' => 'الموردون', 'key' => 'catalog.suppliers', 'url' => '#'],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'التقارير',
            'icon' => 'reports',
            'key' => 'reports',
            'items' => [
                ['label' => 'تقرير المبيعات', 'key' => 'reports.sales', 'url' => '#'],
                ['label' => 'تقرير المشتريات', 'key' => 'reports.purchases', 'url' => '#'],
                ['label' => 'تقرير المخزون', 'key' => 'reports.inventory', 'url' => '#'],
                ['label' => 'تقرير الأرباح', 'key' => 'reports.profit', 'url' => '#'],
            ],
        ],
        [
            'type' => 'group',
            'label' => 'الإدارة',
            'icon' => 'admin',
            'key' => 'admin',
            'items' => [
                ['label' => 'الموظفون', 'key' => 'admin.users', 'url' => '#'],
                ['label' => 'الأدوار والصلاحيات', 'key' => 'admin.roles', 'url' => '#'],
                ['label' => 'إعدادات النظام', 'key' => 'admin.settings', 'url' => '#'],
                ['label' => 'سجل التدقيق', 'key' => 'admin.audit', 'url' => '#'],
            ],
        ],
    ];

    $current = $active ?? 'dashboard';
@endphp

<div class="flex h-full flex-col bg-slate-900 text-slate-300">

    {{-- الهوية --}}
    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
        <div class="grid size-9 place-items-center rounded-lg bg-brand-600 text-white shadow-lg shadow-brand-900/40">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 4.5v15M9 19.5h6M5 8h14M12 4.5 5 8m7-3.5L19 8" />
                <path d="M5 8 2 13.5h6L5 8ZM19 8l-3 5.5h6L19 8Z" />
            </svg>
        </div>
        <div class="leading-tight">
            <p class="text-base font-bold text-white">ميزان</p>
            <p class="text-[11px] text-slate-400">نظام إدارة المخازن والمبيعات</p>
        </div>
    </div>

    {{-- التنقل --}}
    <nav class="scroll-thin flex-1 space-y-1 overflow-y-auto px-3 py-4">
        @foreach ($nav as $entry)
            @if ($entry['type'] === 'link')
                @php $isActive = $current === $entry['key']; @endphp
                <a href="{{ $entry['url'] }}"
                   @class([
                       'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                       'bg-brand-600 text-white shadow-sm' => $isActive,
                       'hover:bg-white/5 hover:text-white' => ! $isActive,
                   ])>
                    <x-icon :name="$entry['icon']" class="size-5 shrink-0" />
                    <span>{{ $entry['label'] }}</span>
                </a>
            @else
                @php
                    $groupActive = collect($entry['items'])->contains(fn ($i) => $i['key'] === $current);
                @endphp
                <div x-data="{ open: @js($groupActive) }" class="select-none">
                    <button type="button" @click="open = ! open"
                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition hover:bg-white/5 hover:text-white"
                            :class="open && 'text-white'">
                        <x-icon :name="$entry['icon']" class="size-5 shrink-0" />
                        <span class="flex-1 text-start">{{ $entry['label'] }}</span>
                        <x-icon name="chevron" class="size-4 shrink-0 transition-transform duration-200"
                                x-bind:class="open && 'rotate-180'" />
                    </button>

                    <div x-show="open" x-cloak x-collapse class="mt-1 space-y-0.5 ps-4">
                        @foreach ($entry['items'] as $item)
                            @php $isActive = $current === $item['key']; @endphp
                            <a href="{{ $item['url'] }}"
                               @class([
                                   'flex items-center gap-2.5 rounded-lg py-2 pe-3 ps-3 text-[13px] transition',
                                   'bg-white/10 font-semibold text-white' => $isActive,
                                   'text-slate-400 hover:bg-white/5 hover:text-white' => ! $isActive,
                               ])>
                                <span @class([
                                    'size-1.5 rounded-full',
                                    'bg-brand-400' => $isActive,
                                    'bg-slate-600' => ! $isActive,
                                ])></span>
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>

    {{-- المستخدم --}}
    <div class="shrink-0 border-t border-white/10 p-3">
        <div class="flex items-center gap-3 rounded-lg px-2 py-2">
            <div class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-700 text-sm font-bold text-white">
                أ
            </div>
            <div class="min-w-0 flex-1 leading-tight">
                <p class="truncate text-sm font-medium text-white">أيوب ميلاد</p>
                <p class="truncate text-[11px] text-slate-400">مدير النظام</p>
            </div>
            <button type="button" title="تسجيل الخروج"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-white/10 hover:text-white">
                <x-icon name="logout" class="size-5" />
            </button>
        </div>
    </div>
</div>
