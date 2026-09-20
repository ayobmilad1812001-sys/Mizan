<x-layouts.app title="جلسات البيع" active="sales.sessions">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-start font-semibold">الجلسة</th>
                        <th class="px-4 py-2.5 text-start font-semibold">الموظف</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الحالة</th>
                        <th class="px-4 py-2.5 text-center font-semibold">عدد الفواتير</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الفرق</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الفتح</th>
                        <th class="px-4 py-2.5 text-center font-semibold">الإغلاق</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sessions as $session)
                        <tr class="cursor-pointer hover:bg-slate-50" onclick="location.href='{{ route('sales-sessions.show', $session) }}'">
                            <td class="nums px-4 py-3 font-medium text-brand-700">#{{ $session->id }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ $session->user->name }}</td>
                            <td class="px-4 py-3 text-center">
                                <span @class([
                                    'rounded px-2 py-1 text-[11px] font-medium',
                                    'bg-emerald-100 text-emerald-700' => $session->isOpen(),
                                    'bg-slate-100 text-slate-600' => ! $session->isOpen(),
                                ])>{{ $session->status->label() }}</span>
                            </td>
                            <td class="nums px-4 py-3 text-center text-slate-700">{{ $session->sales_count }}</td>
                            <td @class([
                                'nums px-4 py-3 text-center font-semibold',
                                'text-slate-400' => $session->difference === null,
                                'text-emerald-600' => $session->difference !== null && bccomp($session->difference, '0', 3) > 0,
                                'text-red-600' => $session->difference !== null && bccomp($session->difference, '0', 3) < 0,
                                'text-slate-700' => $session->difference !== null && bccomp($session->difference, '0', 3) === 0,
                            ])>{{ $session->difference ?? '—' }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-500">{{ $session->opened_at->format('Y-m-d H:i') }}</td>
                            <td class="nums px-4 py-3 text-center text-slate-500">{{ $session->closed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">لا توجد جلسات بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $sessions->links() }}</div>
    </div>

</x-layouts.app>
