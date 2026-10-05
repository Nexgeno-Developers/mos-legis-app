{{-- Page text and SEO from Admin → Pages ("Plagiarism checker" template); the check form is dynamic. --}}
@php
    $meta = fn (string $key) => $page->meta($key);
    // {fee} and {threshold} in the steps come from Settings.
    $fill = fn (?string $text) => strtr((string) $text, ['{fee}' => money($fee), '{threshold}' => rtrim(rtrim(number_format($threshold, 2), '0'), '.')]);
    $steps = collect($page->meta('steps') ?: [])->pluck('text')->filter()->map($fill)->values();
@endphp
<x-layouts.site :title="$page->seo_title ?: $page->title" :description="$page->seo_description ?: $page->excerpt" :og-image="$page->og_image">
    <x-page-header :title="$page->title" :intro="$page->excerpt" />
    <div class="mx-auto grid max-w-[1200px] gap-12 px-4 py-8 sm:px-6 md:py-10 lg:grid-cols-[1.4fr_1fr]">
        <section>
            @auth
                @if (auth()->user()->isAuthor())
                    <form method="POST" action="{{ route('plagiarism-checker.store') }}" enctype="multipart/form-data" class="space-y-5 border border-border bg-card p-8" x-data="{ mode: @js(old('content') ? 'paste' : 'paste') }">
                        @csrf
                        <x-form.input name="title" label="Title / label" placeholder="Draft article on federalism" required />
                        <div class="flex gap-2">
                            <button type="button" @click="mode = 'paste'" :class="mode === 'paste' ? 'border-primary text-primary' : 'border-border'" class="label-caps border px-4 py-2 text-xs">Paste content</button>
                            <button type="button" @click="mode = 'upload'" :class="mode === 'upload' ? 'border-primary text-primary' : 'border-border'" class="label-caps border px-4 py-2 text-xs">Upload .docx</button>
                        </div>
                        <div x-show="mode === 'paste'"><x-form.textarea name="content" label="Content" rows="10" x-bind:disabled="mode !== 'paste'" /></div>
                        <div x-show="mode === 'upload'" x-cloak>
                            <x-form.field label="Document (.docx)" name="document" :hint="'Up to '.App\Support\UploadLimits::label(20480).'.'">
                                <input type="file" name="document" accept=".docx" class="field-input" data-rule-maxbytes="{{ App\Support\UploadLimits::bytes(20480) }}" data-msg-maxbytes="This file is larger than {{ App\Support\UploadLimits::label(20480) }}. Please upload a smaller file." x-bind:disabled="mode !== 'upload'">
                            </x-form.field>
                        </div>
                        <x-button type="submit" variant="primary" icon="scan-search">Continue — {{ money($fee) }}</x-button>
                    </form>
                @else
                    <p class="border-l-2 border-gold/60 bg-card px-4 py-3">The plagiarism checker is available to author accounts.</p>
                @endif
            @else
                <div class="border border-border bg-card p-8">
                    @if ($meta('guest_heading'))<p class="font-display text-2xl">{{ $meta('guest_heading') }}</p>@endif
                    @if ($meta('guest_text'))<p class="mt-2 text-muted-foreground">{{ $meta('guest_text') }}</p>@endif
                    <div class="mt-5 flex gap-3">
                        <x-button variant="primary" icon="log-in" :href="route('login')">Sign in</x-button>
                        <x-button icon="user-plus" :href="route('register')">Create account</x-button>
                    </div>
                </div>
            @endauth
        </section>
        <aside>
            @if ($meta('steps_label') || $meta('steps_heading'))
                <x-section-heading :eyebrow="$meta('steps_label')" :title="$meta('steps_heading')" />
            @endif
            <ol class="mt-6 space-y-4">
                @foreach ($steps as $step)
                    <li class="flex gap-3"><span class="font-mono text-sm text-primary">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span>{{ $step }}</span></li>
                @endforeach
            </ol>
            @if ($meta('steps_note'))<p class="mt-6 text-sm text-muted-foreground">{{ $fill($meta('steps_note')) }}</p>@endif
            @if ($page?->content)<div class="prose-legis mt-8">{!! $page->content !!}</div>@endif
        </aside>
    </div>
</x-layouts.site>
