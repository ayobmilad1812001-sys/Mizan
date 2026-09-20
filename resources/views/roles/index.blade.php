<x-layouts.app title="الأدوار والصلاحيات" active="admin.roles">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @can('roles.create')
        <form method="POST" action="{{ route('roles.store') }}" class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            <h2 class="mb-4 text-sm font-bold text-slate-900">دور جديد</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">اسم الدور</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-slate-600">الوصف (اختياري)</label>
                    <div class="flex gap-2">
                        <input type="text" name="description" value="{{ old('description') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        <button type="submit" class="shrink-0 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">إنشاء</button>
                    </div>
                </div>
            </div>
        </form>
    @endcan

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">الدور</th>
                        <th class="px-4 py-2.5 text-start font-semibold">الوصف</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الموظفون</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الصلاحيات</th>
                        <th class="px-4 py-2.5 text-center font-semibold">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($roles as $role)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $role->name }}
                                @if ($role->is_system)
                                    <span class="ms-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600">نظامي</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $role->description ?? '—' }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-700">{{ $role->users_count }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-700">{{ $role->permissions_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-3">
                                    @can('roles.update')
                                        <a href="{{ route('roles.edit', $role) }}" class="text-xs font-semibold text-brand-700 hover:underline">الصلاحيات</a>
                                    @endcan
                                    @can('roles.delete')
                                        @if (! $role->is_system && $role->users_count === 0)
                                            <form method="POST" action="{{ route('roles.destroy', $role) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">حذف</button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
