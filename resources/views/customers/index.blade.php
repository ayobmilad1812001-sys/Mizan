<x-layouts.app title="العملاء" active="catalog.customers">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <form method="GET" class="relative flex-1 min-w-48">
            <x-icon name="search" class="pointer-events-none absolute inset-y-0 start-3 my-auto size-4 text-slate-400" />
            <input type="search" name="search" value="{{ request('search') }}" placeholder="ابحث بالاسم أو الهاتف"
                   class="w-full rounded-lg border border-slate-300 bg-white py-2 pe-3 ps-9 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" />
        </form>

        @can('customers.create')
            <a href="{{ route('customers.create') }}"
               class="flex shrink-0 items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <x-icon name="plus" class="size-4" /> عميل جديد
            </a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">الاسم</th>
                        <th class="px-4 py-2.5 text-start font-semibold">الهاتف</th>
                        <th class="px-4 py-2.5 text-start font-semibold">البريد</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الحالة</th>
                        <th class="px-4 py-2.5 text-center font-semibold">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $customer->name }}</td>
                            <td class="nums px-4 py-3 text-slate-600">{{ $customer->phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $customer->email ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span @class([
                                    'rounded px-2 py-1 text-[11px] font-medium',
                                    'bg-emerald-100 text-emerald-700' => $customer->status->value === 'active',
                                    'bg-slate-100 text-slate-600' => $customer->status->value !== 'active',
                                ])>{{ $customer->status->label() }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @can('customers.update')
                                        <a href="{{ route('customers.edit', $customer) }}" class="text-slate-400 hover:text-brand-600" title="تعديل">
                                            <x-icon name="edit" class="size-4" />
                                        </a>
                                    @endcan
                                    @can('customers.deactivate')
                                        <form method="POST" action="{{ route('customers.toggle-status', $customer) }}">
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
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">لا يوجد عملاء بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $customers->links() }}</div>
    </div>

</x-layouts.app>
