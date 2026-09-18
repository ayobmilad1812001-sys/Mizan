<x-layouts.app title="المخازن" active="catalog.warehouses">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-4 flex justify-end">
        @can('warehouses.create')
            <a href="{{ route('warehouses.create') }}"
               class="flex items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <x-icon name="plus" class="size-4" /> مخزن جديد
            </a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">الرمز</th>
                        <th class="px-4 py-2.5 text-start font-semibold">الاسم</th>
                        <th class="px-4 py-2.5 text-start font-semibold">المدينة</th>
                        <th class="px-4 py-2.5 text-start font-semibold">المشرف</th>
                        <th class="px-4 py-2.5 text-start font-semibold">الهاتف</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الحالة</th>
                        <th class="px-4 py-2.5 text-center font-semibold">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($warehouses as $warehouse)
                        <tr class="hover:bg-slate-50">
                            <td class="nums px-4 py-3 text-slate-600">{{ $warehouse->code }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $warehouse->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $warehouse->city ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $warehouse->supervisor?->name ?? '—' }}</td>
                            <td class="nums px-4 py-3 text-slate-600">{{ $warehouse->phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span @class([
                                    'rounded px-2 py-1 text-[11px] font-medium',
                                    'bg-emerald-100 text-emerald-700' => $warehouse->status->value === 'active',
                                    'bg-slate-100 text-slate-600' => $warehouse->status->value !== 'active',
                                ])>{{ $warehouse->status->label() }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @can('warehouses.update')
                                        <a href="{{ route('warehouses.edit', $warehouse) }}" class="text-slate-400 hover:text-brand-600" title="تعديل">
                                            <x-icon name="edit" class="size-4" />
                                        </a>
                                    @endcan
                                    @can('warehouses.deactivate')
                                        <form method="POST" action="{{ route('warehouses.toggle-status', $warehouse) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-slate-400 hover:text-brand-600" title="تبديل الحالة">
                                                <x-icon name="power" class="size-4" />
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">لا توجد مخازن بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $warehouses->links() }}</div>
    </div>

</x-layouts.app>
