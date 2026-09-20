<x-layouts.app title="تعديل {{ $user->name }}" active="admin.users">

    <form method="POST" action="{{ route('users.update', $user) }}" class="mx-auto max-w-lg">
        @csrf
        @method('PUT')

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900">بيانات الموظف</h2>
                <span @class([
                    'rounded px-2 py-1 text-[11px] font-medium',
                    'bg-emerald-100 text-emerald-700' => $user->status->value === 'active',
                    'bg-amber-100 text-amber-700' => $user->status->value === 'pending',
                    'bg-red-100 text-red-700' => $user->status->value === 'disabled',
                ])>{{ $user->status->label() }}</span>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">الاسم</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required dir="ltr"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">الهاتف (اختياري)</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" dir="ltr"
                           class="nums w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">الدور</label>
                    <select name="role_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-[11px] text-slate-500">تغيير الدور يسري فوراً على صلاحيات الموظف.</p>
                </div>
            </div>
        </div>

        <div class="mt-5 flex justify-end gap-3">
            <a href="{{ route('users.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">إلغاء</a>
            <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">حفظ</button>
        </div>
    </form>

</x-layouts.app>
