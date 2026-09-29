<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\Settings;
use App\Support\SettingsRegistry;
use Illuminate\Database\Seeder;

/**
 * SOW A.21 default settings (mirrors the seed rows in schema.sql). Existing values are kept.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SettingsRegistry::GROUPS as $group => $definition) {
            foreach ($definition['fields'] as $key => $field) {
                Setting::firstOrCreate(
                    ['setting_group' => $group, 'setting_key' => $key],
                    ['setting_value' => $field['default']],
                );
            }
        }

        app(Settings::class)->flush();
    }
}
