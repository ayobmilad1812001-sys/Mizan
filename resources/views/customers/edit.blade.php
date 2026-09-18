<x-layouts.app title="تعديل عميل" active="catalog.customers">

    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-bold text-slate-900">بيانات العميل</h2>
        <form method="POST" action="{{ route('customers.update', $customer) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf @method('PUT')

            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-600">الاسم</label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">الهاتف (اختياري)</label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" dir="ltr"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm nums" />
                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">البريد الإلكتروني (اختياري)</label>
                <input type="email" name="email" value="{{ old('email', $customer->email) }}" dir="ltr"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-600">العنوان</label>
                <textarea name="address" rows="2"
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('address', $customer->address) }}</textarea>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-600">ملاحظات</label>
                <textarea name="notes" rows="2"
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('notes', $customer->notes) }}</textarea>
            </div>

            <div class="flex justify-end gap-3 sm:col-span-2">
                <a href="{{ route('customers.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">إلغاء</a>
                <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">حفظ</button>
            </div>
        </form>
    </div>

</x-layouts.app>
