@props(['action' => url()->current()])
{{-- GET filter form; every child input is a query-string filter. --}}
<form method="GET" action="{{ $action }}" {{ $attributes->merge(['class' => 'flex flex-wrap items-end gap-3 py-6']) }}>
    {{ $slot }}
    <div class="flex gap-2">
        <x-button type="submit" variant="primary" icon="search">Filter</x-button>
        @if (request()->query())
            <x-button :href="$action" variant="ghost">Reset</x-button>
        @endif
    </div>
</form>
