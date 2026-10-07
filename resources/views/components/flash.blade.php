@php
    // Auth pages print the status message inline (components/auth-card); skip it here so it isn't shown twice.
    $messages = array_filter(['success' => session('success'), 'error' => session('error'), 'status' => app()->bound('flash.status-inline') ? null : session('status')]);
@endphp
@if ($messages || $errors->any())
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)" class="fixed right-4 top-4 z-[60] w-full max-w-sm space-y-2">
        @foreach ($messages as $type => $message)
            <div role="status" class="flex items-start gap-3 border bg-card p-4 shadow-lg {{ $type === 'error' ? 'border-destructive/40 text-destructive' : 'border-success/40 text-success' }}">
                <x-icon :name="$type === 'error' ? 'circle-alert' : 'circle-check'" class="mt-1 h-5 w-5" />
                <p class="flex-1 text-base">{{ $message }}</p>
                <button type="button" @click="show = false" class="text-muted-foreground" aria-label="Dismiss">&times;</button>
            </div>
        @endforeach
        @if ($errors->any() && ! $messages)
            <div role="alert" class="flex items-start gap-3 border border-destructive/40 bg-card p-4 text-destructive shadow-lg">
                <x-icon name="circle-alert" class="mt-1 h-5 w-5" />
                <p class="flex-1 text-base">Please correct the highlighted fields.</p>
                <button type="button" @click="show = false" class="text-muted-foreground" aria-label="Dismiss">&times;</button>
            </div>
        @endif
    </div>
@endif
