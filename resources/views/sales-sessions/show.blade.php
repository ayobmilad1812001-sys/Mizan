<x-layouts.app title="جلسة البيع #{{ $session->id }}" active="sales.sessions">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mx-auto max-w-3xl space-y-5">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="nums text-lg font-bold text-slate-900">جلسة #{{ $session->id }}</p>
                    <p class="text-sm text-slate-500">{{ $session->user->name }}</p>
                </div>
                <span @class([
                    'rounded-full px-3 py-1 text-xs font-semibold',
                    'bg-emerald-100 text-emerald-700' => $session->isOpen(),
                    'bg-slate-100 text-slate-600' => ! $session->isOpen(),
                ])>{{ $session->status->label() }}</span>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                <div>
                    <p class="text-[11px] text-slate-500">العهدة الافتتاحية</p>
                    <p class="nums text-slate-800">{{ $session->opening_float }}</p>
                </div>
                <div>
                    <p class="text-[11px] text-slate-500">النقد المتوقع</p>
                    <p class="nums font-semibold text-slate-900">{{ $expectedCash }}</p>
                </div>
                <div>
                    <p class="text-[11px] text-slate-500">فُتحت</p>
                    <p class="nums text-slate-800">{{ $session->opened_at->format('Y-m-d H:i') }}</p>
                </div>
                <div>
                    <p class="text-[11px] text-slate-500">أُغلقت</p>
                    <p class="nums text-slate-800">{{ $session->closed_at?->format('Y-m-d H:i') ?? '—' }}</p>
                </div>
            </div>

            @unless ($session->isOpen())
                <div class="mt-4 grid grid-cols-3 gap-4 rounded-lg bg-slate-50 px-4 py-3 text-sm">
                    <div>
                        <p class="text-[11px] text-slate-500">المتوقع</p>
                        <p class="nums text-slate-800">{{ $session->expected_cash }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-500">الفعلي</p>
                        <p class="nums text-slate-800">{{ $session->actual_cash }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-500">الفرق</p>
                        <p @class([
                            'nums font-bold',
                            'text-emerald-600' => bccomp($session->difference, '0', 3) > 0,
                            'text-red-600' => bccomp($session->difference, '0', 3) < 0,
                            'text-slate-700' => bccomp($session->difference, '0', 3) === 0,
                        ])>{{ $session->difference }}</p>
                    </div>
                </div>

                @if ($session->closing_notes)
                    <p class="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">{{ $session->closing_notes }}</p>
                @endif
            @endunless
        </div>

        @if ($session->isOpen() && $session->user_id === auth()->id())
            @can('sessions.close')
                <div x-data="{ actual: '' }" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-1 text-sm font-bold text-slate-900">إغلاق الجلسة</h2>
                    <p class="mb-4 text-xs text-slate-500">
                        عُدّ النقد الموجود في الدرج الآن وأدخله. العجز لا يمنع الإغلاق، لكنه يُسجَّل.
                    </p>

                    <form method="POST" action="{{ route('sales-sessions.close', $session) }}">
                        @csrf
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">النقد الفعلي في الدرج</label>
                                <input type="number" step="0.001" min="0" name="actual_cash" x-model="actual" required
                                       class="nums w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                                @error('actual_cash') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">الفرق المتوقع</label>
                                <p class="nums px-1 py-2 text-sm font-semibold"
                                   :class="actual === '' ? 'text-slate-400' : (parseFloat(actual) - {{ $expectedCash }} < 0 ? 'text-red-600' : 'text-emerald-600')"
                                   x-text="actual === '' ? '—' : (parseFloat(actual) - {{ $expectedCash }}).toFixed(3)"></p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="mb-1 block text-xs font-medium text-slate-600">ملاحظات الإغلاق (اختياري)</label>
                            <input type="text" name="closing_notes" value="{{ old('closing_notes') }}"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                                تأكيد الإغلاق
                            </button>
                        </div>
                    </form>
                </div>
            @endcan
        @endif

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-bold text-slate-900">فواتير الجلسة</h2>
            <table class="w-full text-sm">
                <thead class="text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="pb-2 text-start">رقم الفاتورة</th>
                        <th class="pb-2 text-start">العميل</th>
                        <th class="pb-2 text-center">الدفع</th>
                        <th class="pb-2 text-center">الإجمالي</th>
                        <th class="pb-2 text-center">الوقت</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($session->sales as $sale)
                        <tr class="cursor-pointer hover:bg-slate-50" onclick="location.href='{{ route('sales.show', $sale) }}'">
                            <td class="nums py-2 font-medium text-brand-700">{{ $sale->invoice_number }}</td>
                            <td class="py-2 text-slate-700">{{ $sale->customer?->name ?? 'زبون نقدي' }}</td>
                            <td class="py-2 text-center text-slate-600">{{ $sale->payment_method->label() }}</td>
                            <td class="nums py-2 text-center font-semibold text-slate-900">{{ $sale->total }}</td>
                            <td class="nums py-2 text-center text-slate-500">{{ $sale->created_at->format('H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-slate-400">لا توجد فواتير في هذه الجلسة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50/40 p-5 shadow-sm">
            <h2 class="mb-1 text-sm font-bold text-slate-900">مرتجعات الجلسة</h2>
            <p class="mb-4 text-xs text-slate-500">مبالغ خرجت من الدرج، وهي مطروحة من النقد المتوقع أعلاه.</p>
            <table class="w-full text-sm">
                <thead class="text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="pb-2 text-start">رقم المرتجع</th>
                        <th class="pb-2 text-start">الفاتورة الأصلية</th>
                        <th class="pb-2 text-start">السبب</th>
                        <th class="pb-2 text-center">المبلغ المُعاد</th>
                        <th class="pb-2 text-center">الوقت</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-100">
                    @forelse ($session->returns as $return)
                        <tr class="cursor-pointer hover:bg-amber-50" onclick="location.href='{{ route('returns.show', $return) }}'">
                            <td class="nums py-2 font-medium text-brand-700">{{ $return->reference_number }}</td>
                            <td class="nums py-2 text-slate-700">{{ $return->sale->invoice_number }}</td>
                            <td class="py-2 text-slate-600">{{ $return->reason }}</td>
                            <td class="nums py-2 text-center font-semibold text-red-600">-{{ $return->total_amount }}</td>
                            <td class="nums py-2 text-center text-slate-500">{{ $return->created_at->format('H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-slate-400">لا توجد مرتجعات في هذه الجلسة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
