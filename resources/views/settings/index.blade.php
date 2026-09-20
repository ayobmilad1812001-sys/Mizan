@php
    $labels = [
        'company_name' => 'اسم الشركة',
        'company_phone' => 'هاتف الشركة',
        'company_address' => 'عنوان الشركة',
        'company_logo' => 'رابط شعار الشركة',
        'currency' => 'العملة',
        'tax_rate' => 'نسبة الضريبة %',
        'invoice_prefix' => 'بادئة رقم الفاتورة',
    ];
    $sectionLabels = ['company' => 'بيانات الشركة', 'finance' => 'المالية', 'invoice' => 'الفواتير'];
@endphp

<x-layouts.app title="الإعدادات" active="settings">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" class="mx-auto max-w-3xl space-y-5">
        @csrf
        @method('PUT')

        @foreach ($sections as $section => $items)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-bold text-slate-900">{{ $sectionLabels[$section] ?? $section }}</h2>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($items as $setting)
                        <div @class(['sm:col-span-2' => $setting->name === 'company_address'])>
                            <label class="mb-1 flex items-center gap-2 text-xs font-medium text-slate-600">
                                {{ $labels[$setting->name] ?? $setting->name }}
                                @if ($setting->is_critical)
                                    <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700">حسّاس</span>
                                @endif
                            </label>
                            <input type="{{ $setting->name === 'tax_rate' ? 'number' : 'text' }}"
                                   @if ($setting->name === 'tax_rate') step="0.01" min="0" max="100" @endif
                                   name="settings[{{ $setting->name }}]"
                                   value="{{ old('settings.'.$setting->name, $setting->value) }}"
                                   @disabled(! auth()->user()->hasPermission('settings.update'))
                                   @class([
                                       'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm disabled:bg-slate-100',
                                       'nums' => in_array($setting->name, ['tax_rate', 'company_phone', 'invoice_prefix', 'currency'], true),
                                   ]) />
                            @error('settings.'.$setting->name) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>

                @if ($section === 'finance')
                    <p class="mt-4 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
                        تغيير نسبة الضريبة يؤثر على الفواتير الجديدة فقط؛ الفواتير الصادرة تحتفظ بنسبتها وقت إصدارها.
                        وكل تغيير في إعداد حسّاس يُسجَّل في سجل التدقيق.
                    </p>
                @endif
            </div>
        @endforeach

        @can('settings.update')
            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    حفظ الإعدادات
                </button>
            </div>
        @endcan
    </form>

</x-layouts.app>
