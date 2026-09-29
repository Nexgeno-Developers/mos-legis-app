@props(['action', 'label' => 'Delete', 'confirm' => 'Delete this record? This cannot be undone.', 'icon' => 'trash-2'])
<form method="POST" action="{{ $action }}" class="inline" onsubmit="return confirm(@js($confirm))">
    @csrf
    @method('DELETE')
    <button type="submit" class="inline-flex items-center gap-1 text-sm text-destructive hover:underline" title="{{ $label }}">
        <x-icon :name="$icon" /> <span class="sr-only md:not-sr-only">{{ $label }}</span>
    </button>
</form>
