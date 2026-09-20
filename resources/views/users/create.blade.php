<x-layouts.app title="دعوة موظف" active="admin.users">

    <form method="POST" action="{{ route('users.store') }}" class="mx-auto max-w-lg">
        @csrf

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-1 text-sm font-bold text-slate-900">موظف جديد</h2>
            <p class="mb-4 text-xs text-slate-500">
                يُنشأ الحساب موقوفاً حتى يفتح الموظف رابط الدعوة ويضع كلمة سره بنفسه. الرابط صالح 24 ساعة ويُستعمل مرة واحدة.
            </p>

            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">الاسم</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email') }}" required dir="ltr"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">الهاتف (اختياري)</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" dir="ltr"
                           class="nums w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">الدور</label>
                    <select name="role_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">اختر الدور</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="mt-5 flex justify-end gap-3">
            <a href="{{ route('users.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">إلغاء</a>
            <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                إنشاء الحساب وتوليد الدعوة
            </button>
        </div>
    </form>

</x-layouts.app>
