<x-layouts.admin title="Menus">
    <x-admin.heading title="Menus" description="Manage the website’s header and footer navigation. Drag items to reorder them; use groups to build dropdowns (header) and link columns (footer).">
        <x-slot:actions>
            @can('menus.create')
                {{-- "Add group" is hidden for now (no new menu groups are needed). Restore this line to bring it back.
                <x-button icon="folder" @click="$dispatch('open-modal', { name: 'menu-item', record: { link_type: 'none' } })">Add group</x-button>
                --}}
                <x-button variant="primary" icon="plus" @click="$dispatch('open-modal', { name: 'menu-item' })">Add link</x-button>
            @endcan
        </x-slot:actions>
    </x-admin.heading>

    {{-- One tab per menu location --}}
    <nav class="mt-6 flex gap-6 overflow-x-auto overflow-y-hidden border-b border-border" aria-label="Menus">
        @foreach ($menus as $location => $tabMenu)
            <a href="{{ route('admin.menus.index', ['menu' => $location]) }}" @if ($tabMenu->is($menu)) aria-current="page" @endif @class([
                '-mb-px flex items-center gap-2 border-b-2 px-1 py-3 text-base whitespace-nowrap transition-colors',
                'border-primary font-semibold text-primary' => $tabMenu->is($menu),
                'border-transparent text-muted-foreground hover:text-foreground' => ! $tabMenu->is($menu),
            ])>
                {{ $tabMenu->name }}
                <span class="rounded-full bg-secondary px-2 py-0.5 text-xs text-muted-foreground">{{ $tabMenu->items()->count() }}</span>
            </a>
        @endforeach
    </nav>

    <div class="mt-6 grid grid-cols-1 gap-6 2xl:grid-cols-[minmax(0,1fr)_20rem]">
        <section class="min-w-0 border border-border bg-card">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-5 py-4">
                <div>
                    <h2 class="font-display text-xl">{{ $menu->name }}</h2>
                    <p class="text-sm text-muted-foreground">{{ $menu->location->description() }}</p>
                </div>
                <p class="text-sm text-muted-foreground" data-order-status aria-live="polite"></p>
            </div>

            @if ($items->isEmpty())
                <div class="px-5 py-14 text-center">
                    <p class="font-display text-lg">This menu is empty</p>
                    <p class="mt-1 text-sm text-muted-foreground">Add a link, or a group to collect links under one heading.</p>
                </div>
            @endif

            <ol class="space-y-2 p-4 sm:p-5" data-menu-tree data-reorder-url="{{ route('admin.menus.reorder', $menu) }}" @cannot('menus.edit') data-readonly @endcannot>
                @foreach ($items as $item)
                    <li data-id="{{ $item->id }}" data-group="{{ $item->isGroup() ? 1 : 0 }}" @class(['border border-border bg-background', 'opacity-60' => ! $item->isActive()])>
                        @include('admin.menus._row', ['item' => $item])
                        @if ($item->isGroup())
                            <ol data-children class="mx-3 mb-3 ml-10 min-h-12 space-y-2 border-l-2 border-dashed border-gold/50 pl-3 sm:ml-12">
                                @foreach ($item->children as $child)
                                    <li data-id="{{ $child->id }}" data-group="0" @class(['border border-border bg-card', 'opacity-60' => ! $child->isActive()])>
                                        @include('admin.menus._row', ['item' => $child])
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </li>
                @endforeach
            </ol>
        </section>

        <aside class="grid gap-4 self-start md:grid-cols-[minmax(0,1fr)_auto] md:items-start 2xl:grid-cols-1">
            <div class="border border-border bg-card p-5">
                <h2 class="label-caps text-xs text-primary">How menus work</h2>
                <ul class="mt-3 space-y-3 text-sm text-muted-foreground">
                    <li class="flex gap-2"><x-icon name="grip-vertical" class="mt-0.5 h-4 w-4 shrink-0 text-foreground" /> Drag the handle to reorder. Drop a link into a group to place it inside. The order saves automatically.</li>
                    <li class="flex gap-2"><x-icon name="folder" class="mt-0.5 h-4 w-4 shrink-0 text-foreground" /> A <strong class="text-foreground">group</strong> is a heading, not a link: a dropdown in the header, a column in the footer. Empty groups are not shown.</li>
                    <li class="flex gap-2"><x-icon name="eye-off" class="mt-0.5 h-4 w-4 shrink-0 text-foreground" /> <strong class="text-foreground">Hide</strong> keeps an item but removes it from the website. Links to draft or deleted CMS pages are hidden automatically.</li>
                </ul>
            </div>
            <x-button :href="route('home')" target="_blank" rel="noopener" icon="external-link" class="w-full">View website</x-button>
        </aside>
    </div>

    <x-admin.crud-modal name="menu-item" title-add="Add menu item" title-edit="Edit menu item"
        :subtitle="'Adds to the '.$menu->name.'.'"
        :store-url="route('admin.menus.items.store', $menu)"
        :defaults="['label' => '', 'link_type' => 'route', 'route_name' => '', 'page_id' => '', 'url' => '', 'parent_id' => '', 'open_in_new_tab' => false, 'status' => 'Active']">
        <x-form.select name="link_type" label="Type" :options="App\Enums\MenuLinkType::options()" x-model="form.link_type" required />

        <div x-show="form.link_type === 'route'">
            <x-form.select name="route_name" label="Website page" :options="$routes" placeholder="Choose a page" x-model="form.route_name" required
                @change="if (! form.label) form.label = $event.target.selectedOptions[0]?.text ?? ''" />
        </div>
        <div x-show="form.link_type === 'page'" x-cloak>
            <x-form.select name="page_id" label="CMS page" :options="$pages" placeholder="Choose a page" x-model="form.page_id" required
                hint="Pages created under Content → Pages. Draft pages stay hidden until published."
                @change="if (! form.label) form.label = ($event.target.selectedOptions[0]?.text ?? '').replace(' (draft)', '')" />
        </div>
        <div x-show="form.link_type === 'url'" x-cloak>
            <x-form.input name="url" label="Link address" x-model="form.url" required placeholder="https://example.com or /blogs?category=law"
                hint="A full address, a path on this site starting with /, or a mailto:/tel: link." />
        </div>

        <x-form.input name="label" label="Label" x-model="form.label" required maxlength="120"
            x-bind:placeholder="form.link_type === 'none' ? 'e.g. Journal, Resources, Quick Links' : 'Text shown in the menu'" />

        @if ($groups)
            <div x-show="form.link_type !== 'none'">
                <x-form.select name="parent_id" label="Group" :options="$groups" placeholder="None — show at the top level" x-model="form.parent_id"
                    hint="Place this link inside a group (dropdown or footer column)." />
            </div>
        @endif

        <div class="grid gap-5 sm:grid-cols-2 sm:items-end">
            <x-form.select name="status" label="Visibility" :options="['Active' => 'Shown on website', 'Inactive' => 'Hidden']" x-model="form.status" required />
            <label x-show="form.link_type !== 'none'" class="flex h-11 items-center gap-3 text-base">
                <input type="hidden" name="open_in_new_tab" value="0">
                <input type="checkbox" name="open_in_new_tab" value="1" x-model="form.open_in_new_tab" class="h-4 w-4 accent-primary">
                Open in a new tab
            </label>
        </div>
    </x-admin.crud-modal>
</x-layouts.admin>
