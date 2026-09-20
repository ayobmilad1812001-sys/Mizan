<x-layouts.app title="الموظفون" active="admin.users">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if (session('invite_link'))
        <div x-data="{ copied: false }" class="mb-4 rounded-xl border border-brand-200 bg-brand-50 p-4">
            <p class="mb-2 text-xs font-semibold text-brand-900">رابط الدعوة — يظهر مرة واحدة فقط، انسخه الآن</p>
            <div class="flex flex-wrap items-center gap-2">
                <input type="text" readonly value="{{ session('invite_link') }}" x-ref="link"
                       class="flex-1 rounded-lg border border-brand-200 bg-white px-3 py-2 text-xs text-slate-700" dir="ltr" />
                <button type="button" class="rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-700"
                        @click="$refs.link.select(); document.execCommand('copy'); copied = true"
                        x-text="copied ? 'تم النسخ' : 'نسخ الرابط'"></button>
            </div>
        </div>
    @endif

    @can('users.create')
        <div class="mb-4 flex justify-end">
            <a href="{{ route('users.create') }}"
               class="flex items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <x-icon name="plus" class="size-4" /> دعوة موظف
            </a>
        </div>
    @endcan

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">الاسم</th>
                        <th class="px-4 py-2.5 text-start font-semibold">البريد</th>
                        <th class="px-4 py-2.5 text-start font-semibold">الدور</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الحالة</th>
                        <th class="px-4 py-2.5 text-center font-semibold">آخر دخول</th>
                        <th class="px-4 py-2.5 text-center font-semibold">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-slate-600" dir="ltr">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->role->name }}</td>
                            <td class="px-4 py-3 text-center">
                                <span @class([
                                    'rounded px-2 py-1 text-[11px] font-medium',
                                    'bg-emerald-100 text-emerald-700' => $user->status->value === 'active',
                                    'bg-amber-100 text-amber-700' => $user->status->value === 'pending',
                                    'bg-red-100 text-red-700' => $user->status->value === 'disabled',
                                ])>{{ $user->status->label() }}</span>
                            </td>
                            <td class="nums px-4 py-3 text-center text-slate-500">
                                {{ $user->last_login_at?->format('Y-m-d H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    @can('users.update')
                                        <a href="{{ route('users.edit', $user) }}" class="text-xs font-semibold text-brand-700 hover:underline">تعديل</a>
                                    @endcan
                                    @can('users.create')
                                        @if ($user->status->value === 'pending')
                                            <form method="POST" action="{{ route('users.reinvite', $user) }}">
                                                @csrf
                                                <button type="submit" class="text-xs font-semibold text-amber-700 hover:underline">إعادة الدعوة</button>
                                            </form>
                                        @endif
                                    @endcan
                                    @can('users.disable')
                                        @if ($user->status->value !== 'pending' && $user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.toggle-status', $user) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" @class([
                                                    'text-xs font-semibold hover:underline',
                                                    'text-red-600' => $user->status->value === 'active',
                                                    'text-emerald-600' => $user->status->value === 'disabled',
                                                ])>{{ $user->status->value === 'active' ? 'إيقاف' : 'تفعيل' }}</button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">لا يوجد موظفون</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $users->links() }}</div>
    </div>

</x-layouts.app>
