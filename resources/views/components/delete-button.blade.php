@props(['action', 'label' => 'Delete', 'confirm' => 'Delete this record? This cannot be undone.', 'icon' => 'trash-2'])
<form method="POST" action="{{ $action }}" class="inline" data-delete onsubmit="return confirm(@js($confirm))">
    @csrf
    @method('DELETE')
    <button type="submit" class="inline-flex items-center gap-1 text-sm text-destructive hover:underline" title="{{ $label }}">
        <x-icon :name="$icon" /> <span>{{ $label }}</span>
    </button>
</form>
