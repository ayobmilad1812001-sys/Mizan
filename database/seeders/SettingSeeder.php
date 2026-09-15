<?php

namespace Database\Seeders;

use App\Enums\SettingType;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->defaults() as $name => [$value, $type, $section, $critical]) {
            if (Setting::where('name', $name)->exists()) {
                continue;
            }

            (new Setting)->forceFill([
                'name' => $name,
                'value' => $value,
                'type' => $type,
                'section' => $section,
                'is_critical' => $critical,
            ])->save();
        }
    }

    /**
     * Default settings: name => [value, type, section, is_critical].
     *
     * @return array<string, array{0: string|null, 1: SettingType, 2: string, 3: bool}>
     */
    private function defaults(): array
    {
        return [
            'company_name' => ['ميزان', SettingType::String, 'company', false],
            'company_phone' => [null, SettingType::String, 'company', false],
            'company_address' => [null, SettingType::String, 'company', false],
            'company_logo' => [null, SettingType::String, 'company', false],
            'currency' => ['LYD', SettingType::String, 'finance', true],
            'tax_rate' => ['0.00', SettingType::Decimal, 'finance', true],
            'invoice_prefix' => ['INV', SettingType::String, 'invoice', true],
        ];
    }
}
