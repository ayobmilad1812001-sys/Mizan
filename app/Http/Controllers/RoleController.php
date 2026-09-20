<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class RoleController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function index(): View
    {
        $roles = Role::withCount(['users', 'permissions'])->orderBy('name')->get();

        return view('roles.index', compact('roles'));
    }

    public function edit(Role $role): View
    {
        $role->load('permissions');
        $modules = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        $held = $role->permissions->pluck('name')->all();

        return view('roles.edit', compact('role', 'modules', 'held'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')],
            'description' => ['nullable', 'string', 'max:255'],
        ], [], ['name' => 'اسم الدور']);

        $role = new Role;
        $role->forceFill([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) ?: 'role-'.Str::random(6),
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ])->save();

        $this->audit->record(action: 'role.created', auditable: $role, newValues: ['name' => $role->name]);

        return redirect()->route('roles.edit', $role)->with('status', 'أُنشئ الدور. اختر صلاحياته الآن.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $names = $validated['permissions'] ?? [];
        $ids = Permission::whereIn('name', $names)->pluck('id');

        $before = $role->permissions->pluck('name')->sort()->values()->all();

        DB::transaction(function () use ($role, $ids, $before, $names) {
            $role->permissions()->sync($ids);

            $after = collect($names)->sort()->values()->all();

            if ($before !== $after) {
                $this->audit->record(
                    action: 'role.updated',
                    auditable: $role,
                    oldValues: ['permissions' => $before],
                    newValues: ['permissions' => $after],
                );
            }
        });

        return back()->with('status', 'حُدِّثت صلاحيات الدور.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            throw new RuntimeException('لا يمكن حذف دور نظامي.');
        }

        if ($role->users()->exists()) {
            throw new RuntimeException('لا يمكن حذف دور مرتبط بموظفين. انقلهم إلى دور آخر أولاً.');
        }

        $name = $role->name;
        $this->audit->record(action: 'role.deleted', auditable: $role, oldValues: ['name' => $name]);

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('roles.index')->with('status', "حُذف الدور \"{$name}\".");
    }
}
