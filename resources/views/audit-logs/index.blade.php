@php
    $actionLabels = [
        'setting.updated' => 'تعديل إعداد',
        'user.invited' => 'دعوة موظف',
        'user.updated' => 'تعديل موظف',
        'user.disabled' => 'إيقاف موظف',
        'user.enabled' => 'إعادة تفعيل موظف',
        'user.reinvited' => 'إعادة إرسال دعوة',
        'role.created' => 'إنشاء دور',
        'role.updated' => 'تعديل دور',
        'role.deleted' => 'حذف دور',
    ];
@endphp

<x-layouts.app title="سجل التدقيق" active="audit">

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">الإجراء</label>
            <select name="action" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">كل الإجراءات</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $actionLabels[$action] ?? $action }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">الموظف</label>
            <select name="user_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">كل الموظفين</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">تصفية</button>
        @if (request()->hasAny(['action', 'user_id']))
            <a href="{{ route('audit-logs.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">إلغاء التصفية</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">الإجراء</th>
                        <th class="px-4 py-2.5 text-start font-semibold">الموظف</th>
                        <th class="px-4 py-2.5 text-start font-semibold">العنصر</th>
                        <th class="px-4 py-2.5 text-start font-semibold">قبل</th>
                        <th class="px-4 py-2.5 text-start font-semibold">بعد</th>
                        <th class="px-4 py-2.5 text-center font-semibold">من</th>
                        <th class="px-4 py-2.5 text-center font-semibold">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <span class="rounded bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-700">
                                    {{ $actionLabels[$log->action] ?? $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-800">{{ $log->user?->name ?? 'النظام' }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $log->auditable_type }}<span class="nums"> #{{ $log->auditable_id }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">
                                @if ($log->old_values)
                                    @foreach ($log->old_values as $key => $value)
                                        <div class="text-xs"><span class="text-slate-400">{{ $key }}:</span> <span class="nums">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : ($value ?? '—') }}</span></div>
                                    @endforeach
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                @if ($log->new_values)
                                    @foreach ($log->new_values as $key => $value)
                                        <div class="text-xs"><span class="text-slate-400">{{ $key }}:</span> <span class="nums">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : ($value ?? '—') }}</span></div>
                                    @endforeach
                                @else
                                    —
                                @endif
                            </td>
                            <td class="nums px-4 py-3 text-center text-slate-500">{{ $log->ip_address ?? '—' }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-500">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">لا توجد سجلات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $logs->links() }}</div>
    </div>

</x-layouts.app>
