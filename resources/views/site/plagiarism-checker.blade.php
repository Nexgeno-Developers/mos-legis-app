<x-layouts.site title="Plagiarism Checker" description="Check your manuscript for similarity before you submit.">
    <x-page-header eyebrow="Plagiarism Checker" title="Check Your Content for Similarity" intro="Paste your text or upload a .docx. After payment we run it through our plagiarism service and give you a similarity score and a downloadable report." />
    <div class="mx-auto grid max-w-[1200px] gap-12 px-6 py-16 lg:grid-cols-[1.4fr_1fr]">
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
                            <x-form.field label="Document (.docx)" name="document">
                                <input type="file" name="document" accept=".docx" class="field-input" x-bind:disabled="mode !== 'upload'">
                            </x-form.field>
                        </div>
                        <x-button type="submit" variant="primary" icon="scan-search">Continue — {{ money($fee) }} + tax</x-button>
                    </form>
                @else
                    <p class="border-l-2 border-gold/60 bg-card px-4 py-3">The plagiarism checker is available to author accounts.</p>
                @endif
            @else
                <div class="border border-border bg-card p-8">
                    <p class="font-display text-2xl">Sign in to run a check</p>
                    <p class="mt-2 text-muted-foreground">Results and reports are saved to your author account.</p>
                    <div class="mt-5 flex gap-3">
                        <x-button variant="primary" icon="log-in" :href="route('login')">Sign in</x-button>
                        <x-button icon="user-plus" :href="route('register')">Create account</x-button>
                    </div>
                </div>
            @endauth
        </section>
        <aside>
            <x-section-heading eyebrow="How it works" title="What happens after you pay" />
            <ol class="mt-6 space-y-4">
                @foreach (['Pay the checking fee of '.money($fee).' (+ tax for Indian billing addresses).', 'Your content is sent securely to the plagiarism service.', 'See your similarity percentage and matched sources.', 'Download the report. Manuscripts above '.$threshold.'% similarity are not accepted for review.'] as $step)
                    <li class="flex gap-3"><span class="font-mono text-sm text-primary">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span>{{ $step }}</span></li>
                @endforeach
            </ol>
            <p class="mt-6 text-sm text-muted-foreground">Standalone checks never create or change a manuscript submission.</p>
        </aside>
    </div>
</x-layouts.site>
