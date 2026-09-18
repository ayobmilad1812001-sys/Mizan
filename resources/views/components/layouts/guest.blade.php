@props(['title' => 'تسجيل الدخول'])

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — ميزان</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center bg-slate-100 font-sans text-slate-800 antialiased">

<div class="w-full max-w-sm px-4">
    <div class="mb-8 flex flex-col items-center gap-2">
        <div class="grid size-14 place-items-center rounded-xl bg-brand-600 text-white shadow-lg shadow-brand-900/30">
            <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 4.5v15M9 19.5h6M5 8h14M12 4.5 5 8m7-3.5L19 8" />
                <path d="M5 8 2 13.5h6L5 8ZM19 8l-3 5.5h6L19 8Z" />
            </svg>
        </div>
        <p class="text-lg font-bold text-slate-900">ميزان</p>
        <p class="text-xs text-slate-500">نظام إدارة المخازن والمبيعات</p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        {{ $slot }}
    </div>
</div>

</body>
</html>
