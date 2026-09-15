<header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur lg:px-6">

    {{-- زر القائمة للجوال --}}
    <button type="button" @click="sidebarOpen = true"
            class="rounded-lg p-2 text-slate-600 transition hover:bg-slate-100 lg:hidden">
        <x-icon name="menu" class="size-6" />
        <span class="sr-only">فتح القائمة</span>
    </button>

    {{-- عنوان الصفحة --}}
    <div class="min-w-0 flex-1">
        <h1 class="truncate text-lg font-bold text-slate-900">{{ $title ?? 'لوحة التحكم' }}</h1>
        @isset($subtitle)
            <p class="truncate text-xs text-slate-500">{{ $subtitle }}</p>
        @endisset
    </div>

    {{-- البحث --}}
    <div class="relative hidden md:block">
        <x-icon name="search" class="pointer-events-none absolute inset-y-0 start-3 my-auto size-4 text-slate-400" />
        <input type="search" placeholder="ابحث برقم الفاتورة أو اسم المنتج…"
               class="w-72 rounded-lg border border-slate-200 bg-slate-50 py-2 pe-3 ps-9 text-sm text-slate-700 placeholder:text-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20" />
    </div>

    {{-- حالة الجلسة --}}
    <div class="hidden items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 sm:flex">
        <span class="relative flex size-2">
            <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
        </span>
        <span class="text-xs font-semibold text-emerald-800">
            جلسة مفتوحة <span class="nums">#101</span>
        </span>
    </div>

    {{-- التنبيهات --}}
    <button type="button" class="relative rounded-lg p-2 text-slate-600 transition hover:bg-slate-100">
        <x-icon name="bell" class="size-5" />
        <span class="absolute end-1.5 top-1.5 size-2 rounded-full bg-red-500 ring-2 ring-white"></span>
        <span class="sr-only">التنبيهات</span>
    </button>
</header>
