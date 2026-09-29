<div class="mt-8">
    <div class="flex items-center gap-3 text-sm text-muted-foreground"><span class="h-px flex-1 bg-border"></span>or continue with<span class="h-px flex-1 bg-border"></span></div>
    <div class="mt-4 grid gap-3 sm:grid-cols-2">
        <a href="{{ route('social.redirect', 'google') }}" class="flex items-center justify-center gap-2 border border-border bg-background px-4 py-2.5 text-sm hover:border-gold">
            <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true"><path fill="#EA4335" d="M12 10.2v3.9h5.5c-.2 1.3-1.6 3.9-5.5 3.9-3.3 0-6-2.7-6-6.1s2.7-6.1 6-6.1c1.9 0 3.1.8 3.8 1.5l2.6-2.5C16.8 3.3 14.6 2.3 12 2.3 6.7 2.3 2.4 6.6 2.4 12s4.3 9.7 9.6 9.7c5.5 0 9.2-3.9 9.2-9.4 0-.6-.1-1.1-.2-1.6H12Z"/></svg>
            Google
        </a>
        <a href="{{ route('social.redirect', 'orcid') }}" class="flex items-center justify-center gap-2 border border-border bg-background px-4 py-2.5 text-sm hover:border-gold">
            <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="#A6CE39"/><path fill="#fff" d="M8.3 17.2H6.9V7.6h1.4v9.6Zm2.1-9.6h3.8c3.6 0 5.2 2.6 5.2 4.8 0 2.4-1.9 4.8-5.2 4.8h-3.8V7.6Zm1.4 8.3h2.2c3.2 0 3.9-2.4 3.9-3.5 0-1.8-1.1-3.5-3.9-3.5h-2.2v7ZM8.5 5.6a.9.9 0 1 1-1.8 0 .9.9 0 0 1 1.8 0Z"/></svg>
            ORCID
        </a>
    </div>
</div>
