<x-layouts.guest title="تسجيل الدخول">

    <h1 class="mb-1 text-lg font-bold text-slate-900">تسجيل الدخول</h1>
    <p class="mb-5 text-sm text-slate-500">أدخل بريدك الإلكتروني وكلمة السر للمتابعة</p>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">البريد الإلكتروني</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   dir="ltr"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" />
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">كلمة السر</label>
            <input id="password" type="password" name="password" required
                   dir="ltr"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" />
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
            دخول
        </button>
    </form>

</x-layouts.guest>
