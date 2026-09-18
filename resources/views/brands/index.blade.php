<x-layouts.app title="الماركات" active="catalog.brands">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-1">
            <h2 class="mb-4 text-sm font-bold text-slate-900">إضافة ماركة</h2>
            <form method="POST" action="{{ route('brands.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">الاسم</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    إضافة
                </button>
            </form>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">الاسم</th>
                        <th class="px-4 py-2.5 text-center font-semibold">عدد المنتجات</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الحالة</th>
                        <th class="px-4 py-2.5 text-center font-semibold">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($brands as $brand)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-800">{{ $brand->name }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-600">{{ $brand->products_count }}</td>
                            <td class="px-4 py-3 text-center">
                                <span @class([
                                    'rounded px-2 py-1 text-[11px] font-medium',
                                    'bg-emerald-100 text-emerald-700' => $brand->status->value === 'active',
                                    'bg-slate-100 text-slate-600' => $brand->status->value !== 'active',
                                ])>{{ $brand->status->label() }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('brands.toggle-status', $brand) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-slate-400 hover:text-brand-600" title="تبديل الحالة">
                                        <x-icon name="power" class="mx-auto size-4" />
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">لا توجد ماركات بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-4 py-3">{{ $brands->links() }}</div>
        </div>
    </div>

</x-layouts.app>
