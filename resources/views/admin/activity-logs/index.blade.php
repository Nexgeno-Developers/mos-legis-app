<x-layouts.admin title="Activity Logs">
    <x-admin.heading title="Activity Logs" description="Every change made across the console and the manuscript pipeline. Entries older than 30 days are deleted automatically each night.">
        <x-slot:actions>
            @can('activity-logs.delete')
                <form method="POST" action="{{ route('admin.activity-logs.purge') }}" onsubmit="return confirm('Delete all activity logs older than 30 days?')">
                    @csrf @method('DELETE')
                    <x-button type="submit" variant="danger" icon="trash-2" :disabled="$staleCount === 0">Delete logs older than 30 days ({{ $staleCount }})</x-button>
                </form>
            @endcan
        </x-slot:actions>
    </x-admin.heading>

    <x-filter-bar>
        <x-filter.search placeholder="Search remarks…" />
        <x-filter.select name="module" label="Module" :options="$modules" />
        <label class="flex flex-col gap-1">
            <span class="label-caps text-xs text-muted-foreground">Action</span>
            <input type="search" name="action" value="{{ request('action') }}" placeholder="e.g. Updated" class="field-input w-48!">
        </label>
        <x-filter.date-range />
    </x-filter-bar>

    <x-table :columns="['ID', 'User', 'Module', 'Action', 'Record ID', 'Remarks', 'IP Address', 'Date']" :rows="$logs">
        @foreach ($logs as $log)
            <tr>
                <td class="font-mono text-sm">{{ $log->id }}</td>
                <td class="whitespace-nowrap">{{ $log->user?->name ?? 'System' }}</td>
                <td class="whitespace-nowrap">{{ $log->module }}</td>
                <td>
                    {{ $log->action }}
                    @if ($log->payload)
                        <details class="mt-1 text-xs text-muted-foreground">
                            <summary class="cursor-pointer">Details</summary>
                            <pre class="mt-1 max-w-md overflow-x-auto whitespace-pre-wrap font-mono">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                        </details>
                    @endif
                </td>
                <td class="font-mono text-sm">{{ $log->record_id ?? '—' }}</td>
                <td class="text-sm text-muted-foreground">{{ $log->remarks ?? '—' }}</td>
                <td class="font-mono text-sm">{{ $log->ip_address ?? '—' }}</td>
                <td class="whitespace-nowrap text-sm text-muted-foreground">{{ format_date($log->created_at, true) }}</td>
            </tr>
        @endforeach
    </x-table>
    {{ $logs->links() }}
</x-layouts.admin>
