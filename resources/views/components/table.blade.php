@props(['columns' => [], 'empty' => 'No records found.', 'rows' => null])
<div {{ $attributes->merge(['class' => 'overflow-x-auto border border-border bg-card']) }}>
    <table class="w-full min-w-[820px] text-left">
        <thead>
            <tr class="border-b border-border">
                @foreach ($columns as $column)
                    <th class="label-caps px-5 py-4 text-xs font-semibold text-muted-foreground">{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="[&>tr]:border-b [&>tr]:border-border [&>tr:last-child]:border-0 [&>tr:hover]:bg-secondary/60 [&_td]:px-5 [&_td]:py-4 [&_td]:align-top [&_td]:text-base">
            @if ($rows !== null && $rows->isEmpty())
                <tr><td colspan="{{ count($columns) }}" class="py-10! text-center text-muted-foreground">{{ $empty }}</td></tr>
            @else
                {{ $slot }}
            @endif
        </tbody>
    </table>
</div>
