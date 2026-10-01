@props(['orcid' => null, 'locked' => false, 'removable' => false])
{{--
    ORCID iD field filled by authenticating with ORCID (never typed by hand).
    - orcid:     the connected iD, if any
    - locked:    the iD is saved on the profile and cannot be changed
    - removable: allow removing an iD connected during registration
--}}
<div {{ $attributes->merge(['class' => 'space-y-2']) }} data-field
    x-data="orcidConnect(@js(['orcid' => $orcid, 'connectUrl' => route('orcid.redirect'), 'forgetUrl' => $removable ? route('orcid.forget') : null]))">
    <p class="label-caps text-xs text-muted-foreground">ORCID iD <span class="normal-case tracking-normal">(optional)</span></p>

    {{-- Connected --}}
    <div x-show="orcid" x-cloak class="flex flex-wrap items-center gap-3 border border-[#A6CE39]/60 bg-[#A6CE39]/10 px-4 py-3">
        <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="#A6CE39"/><path fill="#fff" d="M8.3 17.2H6.9V7.6h1.4v9.6Zm2.1-9.6h3.8c3.6 0 5.2 2.6 5.2 4.8 0 2.4-1.9 4.8-5.2 4.8h-3.8V7.6Zm1.4 8.3h2.2c3.2 0 3.9-2.4 3.9-3.5 0-1.8-1.1-3.5-3.9-3.5h-2.2v7ZM8.5 5.6a.9.9 0 1 1-1.8 0 .9.9 0 0 1 1.8 0Z"/></svg>
        <div class="min-w-0 flex-1">
            <a :href="'https://orcid.org/' + orcid" target="_blank" rel="noopener" class="block text-base font-medium [overflow-wrap:anywhere] text-foreground hover:text-primary" x-text="'https://orcid.org/' + orcid"></a>
            <p class="flex items-center gap-1 text-xs text-success"><x-icon name="badge-check" class="h-3.5 w-3.5" /> Verified with ORCID</p>
        </div>
        @if ($locked)
            <span class="flex items-center gap-1 text-xs text-muted-foreground" title="A linked ORCID iD cannot be changed"><x-icon name="lock" class="h-3.5 w-3.5" /> Locked</span>
        @elseif ($removable)
            <button type="button" @click="forget()" class="text-sm text-primary hover:underline">Remove</button>
        @endif
    </div>

    {{-- Not connected --}}
    <div x-show="! orcid">
        <button type="button" @click="connect()" :disabled="busy"
            class="flex h-11 w-full items-center justify-center gap-2 border border-border bg-background px-4 text-sm font-medium transition-colors hover:border-[#A6CE39] disabled:opacity-60">
            <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="#A6CE39"/><path fill="#fff" d="M8.3 17.2H6.9V7.6h1.4v9.6Zm2.1-9.6h3.8c3.6 0 5.2 2.6 5.2 4.8 0 2.4-1.9 4.8-5.2 4.8h-3.8V7.6Zm1.4 8.3h2.2c3.2 0 3.9-2.4 3.9-3.5 0-1.8-1.1-3.5-3.9-3.5h-2.2v7ZM8.5 5.6a.9.9 0 1 1-1.8 0 .9.9 0 0 1 1.8 0Z"/></svg>
            <span x-text="busy ? 'Waiting for ORCID…' : 'Connect your ORCID iD'"></span>
        </button>
        <p class="mt-1.5 text-sm text-muted-foreground">Sign in to ORCID in the pop-up and your iD is added automatically. No ORCID yet? <a href="https://orcid.org/register" target="_blank" rel="noopener" class="text-primary hover:underline">Register free</a> or skip this.</p>
    </div>

    <p x-show="error" x-text="error" x-cloak class="text-sm text-destructive" role="alert"></p>
    @error('orcid')<p class="text-sm text-destructive" role="alert" data-server-error>{{ $message }}</p>@enderror
    @if ($locked)
        <p x-show="orcid" class="text-sm text-muted-foreground">A linked ORCID iD can’t be changed. If it’s wrong, write to the editorial office.</p>
    @endif
</div>
