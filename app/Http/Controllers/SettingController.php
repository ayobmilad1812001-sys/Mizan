<?php

namespace App\Http\Controllers;

use App\Enums\SettingType;
use App\Models\Setting;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function index(): View
    {
        $sections = Setting::orderBy('section')->orderBy('name')->get()->groupBy('section');

        return view('settings.index', compact('sections'));
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = Setting::all()->keyBy('name');

        $validated = $request->validate(
            $this->rulesFor($settings),
            [],
            $settings->mapWithKeys(fn ($s) => ["settings.{$s->name}" => $this->labelFor($s->name)])->all(),
        );

        $changed = [];

        DB::transaction(function () use ($validated, $settings, &$changed) {
            foreach ($validated['settings'] ?? [] as $name => $value) {
                $setting = $settings[$name] ?? null;

                if (! $setting) {
                    continue;
                }

                $value = $value === null || $value === '' ? null : (string) $value;

                if ($value === $setting->value) {
                    continue;
                }

                $old = $setting->value;

                $setting->forceFill([
                    'value' => $value,
                    'updated_by' => auth()->id(),
                ])->save();

                $changed[$name] = [$old, $value];

                // Critical settings change the meaning of money and documents, so each
                // one is recorded with its before and after value.
                if ($setting->is_critical) {
                    $this->audit->record(
                        action: 'setting.updated',
                        auditable: $setting,
                        oldValues: [$name => $old],
                        newValues: [$name => $value],
                    );
                }
            }
        });

        return back()->with('status', $changed === []
            ? 'لم يتغيّر شيء.'
            : 'حُفظت الإعدادات ('.count($changed).' تغيير).');
    }

    /**
     * @param  \Illuminate\Support\Collection<string, Setting>  $settings
     * @return array<string, mixed>
     */
    private function rulesFor($settings): array
    {
        $rules = ['settings' => ['required', 'array']];

        foreach ($settings as $setting) {
            $rules["settings.{$setting->name}"] = match (true) {
                $setting->name === 'tax_rate' => ['nullable', 'numeric', 'between:0,100'],
                $setting->name === 'invoice_prefix' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9\-]+$/'],
                $setting->name === 'currency' => ['required', 'string', 'max:10'],
                $setting->type === SettingType::Integer => ['nullable', 'integer'],
                $setting->type === SettingType::Decimal => ['nullable', 'numeric'],
                $setting->type === SettingType::Boolean => ['nullable', 'boolean'],
                default => ['nullable', 'string', 'max:255'],
            };
        }

        return $rules;
    }

    private function labelFor(string $name): string
    {
        return match ($name) {
            'company_name' => 'اسم الشركة',
            'company_phone' => 'هاتف الشركة',
            'company_address' => 'عنوان الشركة',
            'company_logo' => 'شعار الشركة',
            'currency' => 'العملة',
            'tax_rate' => 'نسبة الضريبة',
            'invoice_prefix' => 'بادئة رقم الفاتورة',
            default => $name,
        };
    }
}
