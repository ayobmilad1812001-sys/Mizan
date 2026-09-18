<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseRequest;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $warehouses = Warehouse::with('supervisor')->orderBy('name')->paginate(20);

        return view('warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        $users = User::where('status', 'active')->orderBy('name')->get();

        return view('warehouses.create', compact('users'));
    }

    public function store(WarehouseRequest $request): RedirectResponse
    {
        Warehouse::create($request->validated());

        return redirect()->route('warehouses.index')->with('status', 'أُضيف المخزن بنجاح.');
    }

    public function edit(Warehouse $warehouse): View
    {
        $users = User::where('status', 'active')->orderBy('name')->get();

        return view('warehouses.edit', compact('warehouse', 'users'));
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update($request->validated());

        return back()->with('status', 'حُفظ المخزن.');
    }

    public function toggleStatus(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->forceFill([
            'status' => $warehouse->status->value === 'active' ? 'inactive' : 'active',
        ])->save();

        return back()->with('status', 'تحدّثت حالة المخزن.');
    }
}
