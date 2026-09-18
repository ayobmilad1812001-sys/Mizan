<x-layouts.app title="منتج جديد" active="catalog.products">

    <div x-data="productForm()" class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('products.store') }}">
            @csrf

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-bold text-slate-900">بيانات المنتج</h2>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-600">اسم المنتج</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" />
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku') }}" required dir="ltr"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" />
                        @error('sku') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">حد إعادة الطلب</label>
                        <input type="number" step="0.001" min="0" name="reorder_level" value="{{ old('reorder_level', 0) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" />
                        @error('reorder_level') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">التصنيف</label>
                        <select name="category_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">بلا تصنيف</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">الماركة</label>
                        <select name="brand_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">بلا ماركة</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" @selected(old('brand_id') == $brand->id)>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-600">الوصف (اختياري)</label>
                        <textarea name="description" rows="2"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">وحدات القياس</h2>
                        <p class="text-xs text-slate-500">حدّد وحدة أساس واحدة (عادة "قطعة")، وأضف وحدات جملة إن وجدت</p>
                    </div>
                    <button type="button" @click="addUnit()"
                            class="flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        <x-icon name="plus" class="size-3.5" /> إضافة وحدة
                    </button>
                </div>

                @error('units') <p class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ $message }}</p> @enderror

                <template x-for="(unit, index) in units" :key="unit.uid">
                    <div class="mb-3 grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 sm:grid-cols-12 sm:items-end">
                        <div class="sm:col-span-3">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">اسم الوحدة</label>
                            <input type="text" :name="`units[${index}][name]`" x-model="unit.name" required
                                   class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">المعامل</label>
                            <input type="number" step="0.0001" min="0.0001" :name="`units[${index}][factor]`"
                                   x-model="unit.factor" :readonly="unit.is_base" :required="!unit.is_base"
                                   :class="unit.is_base ? 'w-full rounded-lg border border-slate-200 bg-slate-100 px-2.5 py-1.5 text-sm nums' : 'w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm nums'" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">سعر الشراء</label>
                            <input type="number" step="0.001" min="0" :name="`units[${index}][purchase_price]`" x-model="unit.purchase_price" required
                                   class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm nums" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">سعر البيع</label>
                            <input type="number" step="0.001" min="0" :name="`units[${index}][selling_price]`" x-model="unit.selling_price" required
                                   class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm nums" />
                        </div>
                        <div class="flex items-center gap-1.5 sm:col-span-2">
                            <input type="radio" name="base_unit" :id="`base-${unit.uid}`" @change="setBase(index)" :checked="unit.is_base" />
                            <label :for="`base-${unit.uid}`" class="text-xs text-slate-600">وحدة أساس</label>
                            <input type="hidden" :name="`units[${index}][is_base]`" :value="unit.is_base ? 1 : 0" />
                        </div>
                        <div class="flex justify-end sm:col-span-1">
                            <button type="button" @click="removeUnit(index)" x-show="units.length > 1"
                                    class="text-slate-400 hover:text-red-600" title="حذف الصف">✕</button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('products.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">إلغاء</a>
                <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">حفظ المنتج</button>
            </div>
        </form>
    </div>

    <script>
        function productForm() {
            return {
                units: [
                    { uid: 1, name: 'قطعة', factor: 1, is_base: true, purchase_price: '', selling_price: '' },
                ],
                nextUid: 2,
                addUnit() {
                    this.units.push({ uid: this.nextUid++, name: '', factor: '', is_base: false, purchase_price: '', selling_price: '' });
                },
                removeUnit(index) {
                    const wasBase = this.units[index].is_base;
                    this.units.splice(index, 1);
                    if (wasBase && this.units.length) this.units[0].is_base = true;
                },
                setBase(index) {
                    this.units.forEach((u, i) => {
                        u.is_base = i === index;
                        if (u.is_base) u.factor = 1;
                    });
                },
            };
        }
    </script>

</x-layouts.app>
