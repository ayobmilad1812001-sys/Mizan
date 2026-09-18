<x-layouts.app title="تعديل منتج" active="catalog.products">

    <div class="mx-auto max-w-3xl space-y-5">

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-bold text-slate-900">بيانات المنتج</h2>
            <form method="POST" action="{{ route('products.update', $product) }}">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-600">اسم المنتج</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required dir="ltr"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('sku') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">حد إعادة الطلب</label>
                        <input type="number" step="0.001" min="0" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm nums" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">التصنيف</label>
                        <select name="category_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">بلا تصنيف</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">الماركة</label>
                        <select name="brand_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">بلا ماركة</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-600">الوصف</label>
                        <textarea name="description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">حفظ</button>
                </div>
            </form>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-bold text-slate-900">الوحدات</h2>

            <table class="mb-4 w-full text-sm">
                <thead class="text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="pb-2 text-start">الاسم</th>
                        <th class="pb-2 text-center">المعامل</th>
                        <th class="pb-2 text-center">سعر الشراء</th>
                        <th class="pb-2 text-center">سعر البيع</th>
                        <th class="pb-2 text-center">أساس؟</th>
                        <th class="pb-2 text-center">الحالة</th>
                        <th class="pb-2 text-center">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($product->units as $unit)
                        <tr>
                            <td class="py-2">{{ $unit->name }}</td>
                            <td class="nums py-2 text-center">{{ $unit->factor }}</td>
                            <td class="nums py-2 text-center">{{ $unit->purchase_price }}</td>
                            <td class="nums py-2 text-center">{{ $unit->selling_price }}</td>
                            <td class="py-2 text-center">{{ $unit->is_base ? '✅' : '—' }}</td>
                            <td class="py-2 text-center">
                                <span @class([
                                    'rounded px-2 py-0.5 text-[11px]',
                                    'bg-emerald-100 text-emerald-700' => $unit->status->value === 'active',
                                    'bg-slate-100 text-slate-600' => $unit->status->value !== 'active',
                                ])>{{ $unit->status->label() }}</span>
                            </td>
                            <td class="py-2 text-center">
                                @unless ($unit->is_base)
                                    <form method="POST" action="{{ route('products.units.toggle-status', [$product, $unit]) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-slate-400 hover:text-brand-600"><x-icon name="power" class="mx-auto size-4" /></button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <details class="rounded-lg border border-dashed border-slate-300 p-3">
                <summary class="cursor-pointer text-xs font-semibold text-slate-600">+ إضافة وحدة جديدة</summary>
                <form method="POST" action="{{ route('products.units.store', $product) }}" class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-5">
                    @csrf
                    <input type="text" name="name" placeholder="الاسم" required class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm" />
                    <input type="number" step="0.0001" min="0.0001" name="factor" placeholder="المعامل" required class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm nums" />
                    <input type="number" step="0.001" min="0" name="purchase_price" placeholder="سعر الشراء" required class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm nums" />
                    <input type="number" step="0.001" min="0" name="selling_price" placeholder="سعر البيع" required class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm nums" />
                    <button type="submit" class="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-700">إضافة</button>
                </form>
            </details>
        </div>
    </div>

</x-layouts.app>
