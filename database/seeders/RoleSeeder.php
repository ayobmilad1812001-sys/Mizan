<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use RuntimeException;

class RoleSeeder extends Seeder
{
    /**
     * Default roles. Permissions are applied only when a role is first created,
     * so changes an admin later makes through the interface are never overwritten.
     * The admin role is the exception: it always holds every permission.
     *
     * @var array<string, array{name: string, description: string, is_system: bool, permissions: list<string>|string}>
     */
    public const ROLES = [
        'admin' => [
            'name' => 'مدير النظام',
            'description' => 'صلاحيات كاملة على النظام',
            'is_system' => true,
            'permissions' => '*',
        ],
        'manager' => [
            'name' => 'مدير',
            'description' => 'إدارة العمل ومراجعته دون إدارة الصلاحيات أو تنفيذ عمليات المخزون والبيع',
            'is_system' => false,
            'permissions' => [
                'products.view', 'products.create', 'products.update', 'products.deactivate',
                'categories.manage', 'brands.manage',
                'suppliers.view', 'suppliers.create', 'suppliers.update', 'suppliers.deactivate',
                'customers.view', 'customers.create', 'customers.update', 'customers.deactivate',
                'warehouses.view', 'warehouses.create', 'warehouses.update', 'warehouses.deactivate',
                'inventory.view', 'stock_movements.view',
                'purchases.view', 'purchases.create', 'purchases.confirm',
                'sessions.view_all', 'sales.view_all', 'returns.view_all',
                'reports.sales', 'reports.purchases', 'reports.inventory', 'reports.profit', 'reports.movements',
            ],
        ],
        'sales_employee' => [
            'name' => 'موظف مبيعات',
            'description' => 'البيع والإرجاع وإدارة جلسته',
            'is_system' => false,
            'permissions' => [
                'sessions.start', 'sessions.close',
                'sales.create', 'sales.view_own',
                'returns.create', 'returns.view_own',
                'customers.view', 'customers.create', 'customers.update',
                'products.view', 'inventory.view',
            ],
        ],
        'warehouse_employee' => [
            'name' => 'موظف مخزن',
            'description' => 'عمليات المخزون الفعلية: الاستلام والتسوية والتحويل',
            'is_system' => false,
            'permissions' => [
                'inventory.view', 'inventory.adjust', 'inventory.transfer', 'stock_movements.view',
                'purchases.view', 'purchases.receive',
                'products.view', 'suppliers.view', 'warehouses.view',
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catalogue = Permission::pluck('id', 'name');

        foreach (self::ROLES as $slug => $definition) {
            if (Role::where('slug', $slug)->exists()) {
                continue;
            }

            $role = new Role;
            $role->forceFill([
                'slug' => $slug,
                'name' => $definition['name'],
                'description' => $definition['description'],
                'is_system' => $definition['is_system'],
            ])->save();

            if ($definition['permissions'] !== '*') {
                $role->permissions()->sync($this->permissionIds($definition['permissions'], $catalogue, $slug));
            }
        }

        Role::where('slug', 'admin')->firstOrFail()->permissions()->sync($catalogue->values());
    }

    /**
     * Map permission names to ids, failing loudly on any name missing from the catalogue.
     *
     * @param  list<string>  $names
     * @return list<int>
     */
    private function permissionIds(array $names, Collection $catalogue, string $slug): array
    {
        $unknown = array_diff($names, $catalogue->keys()->all());

        if ($unknown !== []) {
            throw new RuntimeException("Role [$slug] references unknown permissions: ".implode(', ', $unknown));
        }

        return $catalogue->only($names)->values()->all();
    }
}
