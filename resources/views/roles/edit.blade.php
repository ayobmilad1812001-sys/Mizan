@php
    $moduleLabels = [
        'users' => 'الموظفون', 'roles' => 'الأدوار', 'catalog' => 'التعريفات',
        'suppliers' => 'الموردون', 'customers' => 'العملاء', 'warehouses' => 'المخازن',
        'inventory' => 'المخزون', 'purchases' => 'المشتريات', 'sales' => 'المبيعات',
        'reports' => 'التقارير', 'governance' => 'الحوكمة',
    ];
@endphp

<x-layouts.app title="صلاحيات {{ $role->name }}" active="admin.roles">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('roles.update', $role) }}" x-data="{ held: {{ Js::from($held) }} }">
        @csrf
        @method('PUT')

        <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ $role->name }}</h2>
                    <p class="text-xs text-slate-500">{{ $role->description ?? 'بلا وصف' }}</p>
                </div>
                <p class="text-xs text-slate-500">
                    المختار: <span class="nums font-bold text-brand-700" x-text="held.length"></span> صلاحية
                </p>
            </div>

            @if ($role->slug === 'admin')
                <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    دور مدير النظام يُعاد ضبطه على كل الصلاحيات عند تشغيل الـ seeders.
                </p>
            @endif
        </div>

        <div class="space-y-4">
            @foreach ($modules as $module => $permissions)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900">{{ $moduleLabels[$module] ?? $module }}</h3>
                        <button type="button" class="text-xs font-semibold text-brand-700 hover:underline"
                                @click="
                                    const names = {{ Js::from($permissions->pluck('name')) }};
                                    const all = names.every(n => held.includes(n));
                                    held = all ? held.filter(n => !names.includes(n)) : [...new Set([...held, ...names])];
                                ">تحديد الكل / إلغاء</button>
                    </div>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach ($permissions as $permission)
                            <label class="flex cursor-pointer items-start gap-2 rounded-lg border border-slate-200 px-3 py-2 hover:bg-slate-50">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                       x-model="held" class="mt-0.5 size-4 rounded border-slate-300 text-brand-600" />
                                <span>
                                    <span class="block text-sm text-slate-800">{{ $permission->description }}</span>
                                    <span class="block text-[11px] text-slate-400" dir="ltr">{{ $permission->name }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5 flex justify-end gap-3">
            <a href="{{ route('roles.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">رجوع</a>
            <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">حفظ الصلاحيات</button>
        </div>
    </form>

</x-layouts.app>
