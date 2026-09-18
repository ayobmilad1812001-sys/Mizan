<x-layouts.app title="تعديل مخزن" active="catalog.warehouses">

    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-bold text-slate-900">بيانات المخزن</h2>
        <form method="POST" action="{{ route('warehouses.update', $warehouse) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf @method('PUT')

            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-600">اسم المخزن</label>
                <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">الرمز</label>
                <input type="text" name="code" value="{{ old('code', $warehouse->code) }}" required dir="ltr"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm nums" />
                @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">المدينة</label>
                <input type="text" name="city" value="{{ old('city', $warehouse->city) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">الهاتف</label>
                <input type="text" name="phone" value="{{ old('phone', $warehouse->phone) }}" dir="ltr"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm nums" />
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">المشرف (اختياري)</label>
                <select name="supervisor_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">بلا مشرف</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(old('supervisor_id', $warehouse->supervisor_id) == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-600">العنوان</label>
                <textarea name="address" rows="2"
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('address', $warehouse->address) }}</textarea>
            </div>

            <div class="flex justify-end gap-3 sm:col-span-2">
                <a href="{{ route('warehouses.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">إلغاء</a>
                <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">حفظ</button>
            </div>
        </form>
    </div>

</x-layouts.app>
