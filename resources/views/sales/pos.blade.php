@php
    // Computed before the component so the @json calls below stay simple: Blade's
    // directive parser mis-reads a nested multi-line closure passed straight to @json.
    $productsForJs = $products->map(fn ($p) => [
        'id' => $p->id,
        'sku' => $p->sku,
        'name' => $p->name,
        'units' => $p->units->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'factor' => (string) $u->factor,
            'price' => (string) $u->selling_price,
        ])->values(),
    ]);
@endphp

<x-layouts.app title="نقطة البيع" active="sales.pos">

    @if (! $session)
        <div class="mx-auto max-w-lg rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
            <h2 class="text-base font-bold text-slate-900">لا توجد جلسة بيع مفتوحة</h2>
            <p class="mt-2 text-sm text-slate-500">
                افتح جلسة وأدخل النقد الموجود في الدرج الآن، حتى يمكن حساب الفرق عند الإغلاق.
            </p>

            <form method="POST" action="{{ route('sales-sessions.store') }}" class="mt-5 text-start">
                @csrf
                <label class="mb-1 block text-xs font-medium text-slate-600">العهدة الافتتاحية</label>
                <input type="number" step="0.001" min="0" name="opening_float" value="{{ old('opening_float', '0') }}" required
                       class="nums w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                @error('opening_float') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                <button type="submit" class="mt-4 w-full rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                    فتح الجلسة
                </button>
            </form>
        </div>
    @else
        <div x-data="till()" class="grid grid-cols-1 gap-5 lg:grid-cols-3">

            <form id="sale-form" method="POST" action="{{ route('sales.store') }}" class="lg:col-span-2">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) Str::uuid()) }}">

                <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">المخزن</label>
                            <select name="warehouse_id" x-model.number="warehouseId" required
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">اختر المخزن</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                            @error('warehouse_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">العميل (اختياري)</label>
                            <select name="customer_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">زبون نقدي</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">طريقة الدفع</label>
                            <select name="payment_method" x-model="paymentMethod" required
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @foreach (App\Enums\PaymentMethod::cases() as $method)
                                    <option value="{{ $method->value }}" @selected(old('payment_method', 'cash') === $method->value)>{{ $method->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <p :class="paymentMethod === 'cash' && 'hidden'"
                       class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
                        الإرجاع مسموح للفواتير النقدية فقط — هذه الفاتورة لن تقبل مرتجعاً لاحقاً.
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-900">الأصناف</h2>
                        <button type="button" @click="addLine()"
                                class="flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                            <x-icon name="plus" class="size-3.5" /> إضافة صنف
                        </button>
                    </div>

                    @error('items') <p class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ $message }}</p> @enderror

                    <template x-for="(line, index) in lines" :key="line.uid">
                        <div class="mb-3 grid grid-cols-1 gap-2 rounded-lg border border-slate-200 p-3 sm:grid-cols-12 sm:items-end">
                            <div class="sm:col-span-4">
                                <label class="mb-1 block text-[11px] font-medium text-slate-500">المنتج</label>
                                {{-- No name: the product is implied by the unit, and the server reads it from there. --}}
                                <select x-model.number="line.product_id"
                                        @change="line.product_unit_id = ''" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm">
                                    <option value="">اختر</option>
                                    <template x-for="p in products" :key="p.id">
                                        <option :value="p.id" x-text="p.sku + ' — ' + p.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-[11px] font-medium text-slate-500">الوحدة</label>
                                <select :name="`items[${index}][product_unit_id]`" x-model.number="line.product_unit_id" required
                                        class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm">
                                    <option value="">اختر</option>
                                    <template x-for="u in unitsOf(line.product_id)" :key="u.id">
                                        <option :value="u.id" x-text="u.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-[11px] font-medium text-slate-500">الكمية</label>
                                <input type="number" step="0.001" min="0.001" :name="`items[${index}][quantity]`" x-model="line.quantity" required
                                       class="nums w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm" />
                            </div>
                            <div class="sm:col-span-1">
                                <label class="mb-1 block text-[11px] font-medium text-slate-500">السعر</label>
                                <p class="nums px-1 py-1.5 text-sm text-slate-700" x-text="unitPrice(line)"></p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-[11px] font-medium text-slate-500">المتاح</label>
                                <p class="nums px-1 py-1.5 text-sm" :class="shortfall(line) ? 'font-semibold text-red-600' : 'text-slate-700'"
                                   x-text="available(line.product_id)"></p>
                            </div>
                            <div class="flex justify-end sm:col-span-1">
                                <button type="button" @click="removeLine(index)" x-show="lines.length > 1"
                                        class="text-slate-400 hover:text-red-600" title="حذف">✕</button>
                            </div>
                        </div>
                    </template>

                    <p :class="! lines.some(l => shortfall(l)) && 'hidden'"
                       class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">
                        كمية مطلوبة أكبر من رصيد المخزن — سيرفض النظام الحفظ.
                    </p>
                    <p class="mt-3 text-[11px] text-slate-400">
                        الأسعار تُقرأ من بطاقة المنتج ولا تُعدَّل من هذه الشاشة؛ التخفيض يكون بنسبة خصم على الفاتورة.
                    </p>
                </div>
            </form>

            {{-- ملخص الفاتورة --}}
            <div class="lg:col-span-1">
                <div class="sticky top-20 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-bold text-slate-900">ملخّص الفاتورة</h2>

                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">الإجمالي قبل الخصم</dt>
                            <dd class="nums font-medium text-slate-800" x-text="subtotal()"></dd>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">نسبة الخصم %</label>
                            {{-- Outside the <form> element, bound to it by id so it still submits. --}}
                            <input type="number" step="0.01" min="0" max="100" name="discount_percent" form="sale-form" x-model="discountPercent"
                                   class="nums w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">قيمة الخصم</dt>
                            <dd class="nums font-medium text-red-600" x-text="'-' + discountAmount()"></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">الضريبة (<span class="nums" x-text="taxRate"></span>%)</dt>
                            <dd class="nums font-medium text-slate-800" x-text="taxAmount()"></dd>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-2">
                            <dt class="font-bold text-slate-900">الإجمالي</dt>
                            <dd class="nums text-lg font-bold text-brand-700" x-text="total()"></dd>
                        </div>
                    </dl>

                    <div class="mt-4">
                        <label class="mb-1 block text-xs font-medium text-slate-600">ملاحظات (اختياري)</label>
                        <input type="text" name="notes" form="sale-form" value="{{ old('notes') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                    </div>

                    <button type="submit" form="sale-form"
                            class="mt-5 w-full rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                        إتمام البيع
                    </button>

                    <p class="mt-3 text-center text-[11px] text-slate-400">
                        الجلسة <span class="nums">#{{ $session->id }}</span> — الأسعار والإجماليات تُعاد محاسبتها في الخادم
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if ($session)
        <script>
            function till() {
                return {
                    products: @json($productsForJs),
                    balances: @json($balances),
                    taxRate: '{{ $taxRate }}',
                    warehouseId: {{ (int) old('warehouse_id') }} || '',
                    paymentMethod: '{{ old('payment_method', 'cash') }}',
                    discountPercent: '{{ old('discount_percent', '0') }}',
                    lines: [{ uid: 1, product_id: '', product_unit_id: '', quantity: '' }],
                    nextUid: 2,
                    unitsOf(productId) {
                        return this.products.find(p => p.id === productId)?.units ?? [];
                    },
                    unitOf(line) {
                        return this.unitsOf(line.product_id).find(u => u.id === line.product_unit_id);
                    },
                    unitPrice(line) {
                        return this.unitOf(line)?.price ?? '—';
                    },
                    available(productId) {
                        if (!this.warehouseId || !productId) return '—';
                        return this.balances[`${this.warehouseId}:${productId}`] ?? '0.000';
                    },
                    shortfall(line) {
                        const balance = this.available(line.product_id);
                        const unit = this.unitOf(line);
                        if (balance === '—' || !unit || !line.quantity) return false;
                        return parseFloat(line.quantity) * parseFloat(unit.factor) > parseFloat(balance);
                    },
                    subtotal() {
                        return this.lines.reduce((sum, line) => {
                            const unit = this.unitOf(line);
                            if (!unit || !line.quantity) return sum;
                            return sum + Math.round(parseFloat(line.quantity) * parseFloat(unit.price) * 1000) / 1000;
                        }, 0).toFixed(3);
                    },
                    discountAmount() {
                        const value = parseFloat(this.subtotal()) * (parseFloat(this.discountPercent) || 0) / 100;
                        return (Math.round(value * 1000) / 1000).toFixed(3);
                    },
                    taxAmount() {
                        const base = parseFloat(this.subtotal()) - parseFloat(this.discountAmount());
                        const value = base * (parseFloat(this.taxRate) || 0) / 100;
                        return (Math.round(value * 1000) / 1000).toFixed(3);
                    },
                    total() {
                        return (parseFloat(this.subtotal()) - parseFloat(this.discountAmount()) + parseFloat(this.taxAmount())).toFixed(3);
                    },
                    addLine() {
                        this.lines.push({ uid: this.nextUid++, product_id: '', product_unit_id: '', quantity: '' });
                    },
                    removeLine(index) {
                        this.lines.splice(index, 1);
                    },
                };
            }
        </script>
    @endif

</x-layouts.app>
