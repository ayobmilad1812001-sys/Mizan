@props([
    'title' => 'لوحة التحكم',
    'subtitle' => null,
    'active' => 'dashboard',
])

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — ميزان</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased">

<div x-data="{ sidebarOpen: false }" class="min-h-screen">

    {{-- طبقة التعتيم على الجوال --}}
    <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"></div>

    {{-- القائمة الجانبية — على اليمين --}}
    <aside class="fixed inset-y-0 start-0 z-50 w-72 translate-x-full transition-transform duration-200 lg:translate-x-0"
           :class="sidebarOpen && '!translate-x-0'">
        @include('partials.sidebar', ['active' => $active])
    </aside>

    {{-- المحتوى --}}
    <div class="lg:ms-72">
        @include('partials.topbar', ['title' => $title, 'subtitle' => $subtitle])

        <main class="p-4 lg:p-6">
            @if (session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

</body>
</html>
