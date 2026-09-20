<x-layouts.guest title="تفعيل الحساب">

    <div class="mb-6 text-center">
        <h1 class="text-xl font-bold text-slate-900">مرحباً {{ $user->name }}</h1>
        <p class="mt-1 text-sm text-slate-500">اختر كلمة سر لحسابك لتتمكّن من الدخول.</p>
    </div>

    <form method="POST" action="{{ route('invitations.show', $token) }}" class="space-y-4">
        @csrf

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">البريد الإلكتروني</label>
            <input type="email" value="{{ $user->email }}" disabled dir="ltr"
                   class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500" />
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">كلمة السر</label>
            <input type="password" name="password" required autofocus
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            <p class="mt-1 text-[11px] text-slate-500">١٢ حرفاً على الأقل، وتحتوي حروفاً وأرقاماً.</p>
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">تأكيد كلمة السر</label>
            <input type="password" name="password_confirmation" required
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
        </div>

        <button type="submit" class="w-full rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
            تفعيل الحساب
        </button>
    </form>

</x-layouts.guest>
