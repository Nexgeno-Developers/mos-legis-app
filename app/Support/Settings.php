<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Cached read/write access to the settings table. Keys are "group.key",
 * e.g. settings('manuscript.plagiarism_prescreening_fee').
 */
class Settings
{
    private const CACHE_KEY = 'app.settings';

    /** @var array<string, string|null>|null */
    private ?array $values = null;

    public function get(string $key, mixed $default = null): mixed
    {
        $values = $this->all();

        if (array_key_exists($key, $values) && $values[$key] !== null && $values[$key] !== '') {
            return $values[$key];
        }

        [$group, $field] = array_pad(explode('.', $key, 2), 2, null);

        return $default ?? SettingsRegistry::field((string) $group, (string) $field)['default'] ?? null;
    }

    public function bool(string $key): bool
    {
        return filter_var($this->get($key, '0'), FILTER_VALIDATE_BOOLEAN);
    }

    public function float(string $key): float
    {
        return (float) $this->get($key, '0');
    }

    /** @param array<string, array<string, string|null>> $groups group => [key => value] */
    public function update(array $groups): void
    {
        foreach ($groups as $group => $fields) {
            foreach ($fields as $key => $value) {
                Setting::updateOrCreate(
                    ['setting_group' => $group, 'setting_key' => $key],
                    ['setting_value' => $value],
                );
            }
        }

        $this->flush();
    }

    /** @return array<string, string|null> */
    public function all(): array
    {
        return $this->values ??= Cache::rememberForever(self::CACHE_KEY, fn () => Setting::query()
            ->get(['setting_group', 'setting_key', 'setting_value'])
            ->mapWithKeys(fn (Setting $s) => ["{$s->setting_group->value}.{$s->setting_key}" => $s->setting_value])
            ->all());
    }

    public function flush(): void
    {
        $this->values = null;
        Cache::forget(self::CACHE_KEY);
    }
}
