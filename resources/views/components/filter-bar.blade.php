@props(['action' => url()->current()])
{{-- GET filter form; every child control is a labelled query-string filter aligned on one baseline. --}}
<form method="GET" action="{{ $action }}" {{ $attributes->merge(['class' => 'flex flex-wrap items-end gap-x-3 gap-y-4 py-6']) }}>
    {{ $slot }}
    <div class="flex w-full gap-2 sm:w-auto">
        <x-button type="submit" variant="primary" icon="search" class="flex-1 sm:flex-none">Filter</x-button>
        @if (collect(request()->query())->except('page')->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty())
            <x-button :href="$action" icon="x">Reset</x-button>
        @endif
    </div>
</form>
