<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', '%'.$request->string('search').'%')
                ->orWhere('phone', 'like', '%'.$request->string('search').'%')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $customer = new Customer($request->validated());
        $customer->created_by = auth()->id();
        $customer->save();

        return redirect()->route('customers.index')->with('status', 'أُضيف العميل بنجاح.');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return back()->with('status', 'حُفظت بيانات العميل.');
    }

    public function toggleStatus(Customer $customer): RedirectResponse
    {
        $customer->forceFill([
            'status' => $customer->status->value === 'active' ? 'inactive' : 'active',
        ])->save();

        return back()->with('status', 'تحدّثت حالة العميل.');
    }
}
