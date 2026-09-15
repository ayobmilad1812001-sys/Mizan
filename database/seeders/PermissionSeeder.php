<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * The system permission catalogue, grouped by module.
     *
     * @var array<string, array<string, string>>
     */
    public const PERMISSIONS = [
        'users' => [
            'users.view' => 'عرض الموظفين',
            'users.create' => 'إضافة موظف ودعوته',
            'users.update' => 'تعديل بيانات موظف وتغيير دوره',
            'users.disable' => 'إيقاف حساب موظف وإعادة تفعيله',
        ],
        'roles' => [
            'roles.view' => 'عرض الأدوار',
            'roles.create' => 'إنشاء دور',
            'roles.update' => 'تعديل دور وصلاحياته',
            'roles.delete' => 'حذف دور',
        ],
        'catalog' => [
            'products.view' => 'عرض المنتجات',
            'products.create' => 'إضافة منتج',
            'products.update' => 'تعديل منتج',
            'products.deactivate' => 'إيقاف منتج وإعادة تفعيله',
            'categories.manage' => 'إدارة التصنيفات',
            'brands.manage' => 'إدارة الماركات',
        ],
        'suppliers' => [
            'suppliers.view' => 'عرض الموردين',
            'suppliers.create' => 'إضافة مورد',
            'suppliers.update' => 'تعديل مورد',
            'suppliers.deactivate' => 'إيقاف مورد',
        ],
        'customers' => [
            'customers.view' => 'عرض العملاء',
            'customers.create' => 'إضافة عميل',
            'customers.update' => 'تعديل بيانات تواصل العميل',
            'customers.deactivate' => 'إيقاف عميل',
        ],
        'warehouses' => [
            'warehouses.view' => 'عرض المخازن',
            'warehouses.create' => 'إضافة مخزن',
            'warehouses.update' => 'تعديل مخزن',
            'warehouses.deactivate' => 'إيقاف مخزن',
        ],
        'inventory' => [
            'inventory.view' => 'عرض أرصدة المخزون',
            'inventory.adjust' => 'تسوية المخزون',
            'inventory.transfer' => 'تحويل بين المخازن',
            'stock_movements.view' => 'عرض حركات المخزون',
        ],
        'purchases' => [
            'purchases.view' => 'عرض أوامر الشراء',
            'purchases.create' => 'إنشاء أمر شراء',
            'purchases.confirm' => 'تأكيد أمر شراء',
            'purchases.receive' => 'استلام البضاعة',
        ],
        'sales' => [
            'sessions.start' => 'فتح جلسة بيع',
            'sessions.close' => 'إغلاق جلسة بيع',
            'sessions.view_all' => 'عرض جلسات كل الموظفين',
            'sales.create' => 'إنشاء فاتورة بيع',
            'sales.view_own' => 'عرض مبيعاته فقط',
            'sales.view_all' => 'عرض كل المبيعات',
            'returns.create' => 'تنفيذ إرجاع واسترجاع نقدي',
            'returns.view_own' => 'عرض إرجاعاته فقط',
            'returns.view_all' => 'عرض كل الإرجاعات',
        ],
        'reports' => [
            'reports.sales' => 'تقرير المبيعات',
            'reports.purchases' => 'تقرير المشتريات',
            'reports.inventory' => 'تقرير المخزون',
            'reports.profit' => 'تقرير الأرباح',
            'reports.movements' => 'تقرير حركات المخزون',
        ],
        'governance' => [
            'settings.view' => 'عرض إعدادات النظام',
            'settings.update' => 'تعديل إعدادات النظام',
            'audit.view' => 'عرض سجل التدقيق',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::PERMISSIONS as $module => $permissions) {
            foreach ($permissions as $name => $description) {
                Permission::updateOrCreate(
                    ['name' => $name],
                    ['module' => $module, 'description' => $description],
                );
            }
        }

        $this->command?->info(Permission::count().' permissions in catalogue.');
    }
}
