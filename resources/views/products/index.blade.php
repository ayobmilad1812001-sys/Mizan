<x-layouts.app title="المنتجات" active="catalog.products">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <form method="GET" class="flex flex-1 flex-wrap items-center gap-2">
            <div class="relative flex-1 min-w-48">
                <x-icon name="search" class="pointer-events-none absolute inset-y-0 start-3 my-auto size-4 text-slate-400" />
                <input type="search" name="search" value="{{ request('search') }}" placeholder="ابحث بالاسم أو SKU"
                       class="w-full rounded-lg border border-slate-300 bg-white py-2 pe-3 ps-9 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" />
            </div>
            <select name="category_id" class="rounded-lg border border-slate-300 py-2 px-3 text-sm">
                <option value="">كل التصنيفات</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="brand_id" class="rounded-lg border border-slate-300 py-2 px-3 text-sm">
                <option value="">كل الماركات</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>{{ $brand->name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-lg border border-slate-300 py-2 px-3 text-sm">
                <option value="">كل الحالات</option>
                <option value="active" @selected(request('status') === 'active')>نشط</option>
                <option value="inactive" @selected(request('status') === 'inactive')>موقوف</option>
            </select>
            <button type="submit" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">
                <x-icon name="filter" class="size-4" />
            </button>
        </form>

        @can('products.create')
            <a href="{{ route('products.create') }}"
               class="flex shrink-0 items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <x-icon name="plus" class="size-4" /> منتج جديد
            </a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">SKU</th>
                        <th class="px-4 py-2.5 text-start font-semibold">الاسم</th>
                        <th class="px-4 py-2.5 text-start font-semibold">التصنيف</th>
                        <th class="px-4 py-2.5 text-start font-semibold">الماركة</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الوحدات</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الحالة</th>
                        <th class="px-4 py-2.5 text-center font-semibold">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                        <tr class="hover:bg-slate-50">
                            <td class="nums px-4 py-3 text-slate-600">{{ $product->sku }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $product->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $product->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $product->brand?->name ?? '—' }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-600">{{ $product->units->count() }}</td>
                            <td class="px-4 py-3 text-center">
                                <span @class([
                                    'rounded px-2 py-1 text-[11px] font-medium',
                                    'bg-emerald-100 text-emerald-700' => $product->status->value === 'active',
                                    'bg-slate-100 text-slate-600' => $product->status->value !== 'active',
                                ])>{{ $product->status->label() }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @can('products.update')
                                        <a href="{{ route('products.edit', $product) }}" class="text-slate-400 hover:text-brand-600" title="تعديل">
                                            <x-icon name="edit" class="size-4" />
                                        </a>
                                    @endcan
                                    @can('products.deactivate')
                                        <form method="POST" action="{{ route('products.toggle-status', $product) }}">
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
                        <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">لا توجد منتجات بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $products->links() }}</div>
    </div>

</x-layouts.app>
