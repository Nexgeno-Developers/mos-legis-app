<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Support\Settings;
use App\Support\SettingsRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * SOW A.21 — General, Manuscript, Payment and SEO & Social settings.
 */
class SettingController extends Controller
{
    public function edit(Settings $settings): View
    {
        Gate::authorize('settings.view');

        return view('admin.settings.edit', [
            'groups' => SettingsRegistry::GROUPS,
            'values' => $settings->all(),
            'timezones' => array_combine(timezone_identifiers_list(), timezone_identifiers_list()),
        ]);
    }

    public function update(SettingRequest $request, Settings $settings): RedirectResponse
    {
        $current = $settings->all();
        $changes = [];

        foreach (SettingsRegistry::GROUPS as $group => $definition) {
            foreach ($definition['fields'] as $key => $field) {
                $input = "{$group}.{$key}";

                $value = match ($field['type']) {
                    'boolean' => $request->boolean(str_replace('.', '_', $input)) ? '1' : '0',
                    'image' => $request->hasFile(str_replace('.', '_', $input))
                        ? $request->file(str_replace('.', '_', $input))->store('settings', 'public')
                        : ($current[$input] ?? ''),
                    default => (string) ($request->validated(str_replace('.', '_', $input)) ?? ''),
                };

                if ($field['type'] === 'image' && $value !== ($current[$input] ?? '') && ! empty($current[$input])) {
                    Storage::disk('public')->delete($current[$input]);
                }

                $changes[$group][$key] = $value;
            }
        }

        $settings->update($changes);

        activity()->log('Settings', 'Updated settings', null, $request->safe()->except(array_keys($request->allFiles())));

        return back()->with('success', 'Settings saved.');
    }
}
