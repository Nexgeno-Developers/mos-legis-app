<?php

namespace App\Http\Requests\Admin;

use App\Enums\MenuLinkType;
use App\Enums\RecordStatus;
use App\Models\Menu;
use App\Support\SiteRoutes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Menus are two levels deep: a top-level item is a link or a group; links may sit inside a group.
 */
class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('menuItem') ? 'menus.edit' : 'menus.create');
    }

    public function rules(): array
    {
        $type = $this->enum('link_type', MenuLinkType::class);

        return [
            'label' => ['required', 'string', 'max:120'],
            'link_type' => ['required', Rule::enum(MenuLinkType::class)],
            'route_name' => [Rule::requiredIf($type === MenuLinkType::Route), 'nullable', Rule::in(array_keys(SiteRoutes::options()))],
            'page_id' => [Rule::requiredIf($type === MenuLinkType::Page), 'nullable', 'integer', Rule::exists('pages', 'id')],
            'url' => [Rule::requiredIf($type === MenuLinkType::Url), 'nullable', 'string', 'max:500', 'regex:#^(https?://[^\s]+|/[^\s]*|mailto:[^\s]+|tel:[+\d\s-]+)$#i'],
            'parent_id' => ['nullable', 'integer', Rule::exists('menu_items', 'id')->where('menu_id', $this->menu()->id)->where('link_type', MenuLinkType::None->value)->whereNull('parent_id')],
            'open_in_new_tab' => ['boolean'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'route_name.required' => 'Choose the website page to link to.',
            'page_id.required' => 'Choose the CMS page to link to.',
            'url.required' => 'Enter the link address.',
            'url.regex' => 'Enter a full address (https://…), a site path starting with /, or a mailto:/tel: link.',
            'parent_id.exists' => 'Choose one of this menu’s groups.',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            $item = $this->route('menuItem');

            if ($this->input('link_type') === MenuLinkType::None->value && $this->filled('parent_id')) {
                $validator->errors()->add('parent_id', 'A group cannot be placed inside another group.');
            }

            if ($item && $this->input('link_type') !== MenuLinkType::None->value && $item->children()->exists()) {
                $validator->errors()->add('link_type', 'This group still contains links. Move or delete them before changing its type.');
            }
        }];
    }

    /** The menu being edited: from the route on create, from the item on update. */
    public function menu(): Menu
    {
        return $this->route('menu') ?? $this->route('menuItem')->menu;
    }

    /** Validated data with fields that do not apply to the chosen link type cleared. */
    public function itemData(): array
    {
        $data = $this->validated();
        $type = MenuLinkType::from($data['link_type']);

        return array_merge($data, [
            'route_name' => $type === MenuLinkType::Route ? $data['route_name'] : null,
            'page_id' => $type === MenuLinkType::Page ? $data['page_id'] : null,
            'url' => $type === MenuLinkType::Url ? $data['url'] : null,
            'parent_id' => $type === MenuLinkType::None ? null : ($data['parent_id'] ?? null),
            'open_in_new_tab' => $type !== MenuLinkType::None && $this->boolean('open_in_new_tab'),
        ]);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'parent_id' => $this->input('parent_id') ?: null,
            'page_id' => $this->input('page_id') ?: null,
            'route_name' => $this->input('route_name') ?: null,
            'url' => trim((string) $this->input('url')) ?: null,
        ]);
    }
}
