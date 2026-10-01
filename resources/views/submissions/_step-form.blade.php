{{--
    SOW B.04 / C.08 — multi-step manuscript submission form for signed-in authors.
    Author category, institution and country come from the author's profile (shown as "Submitting as").
    Expects $authorCategories (active, id => name), $contentCategories (models with ->currentTheme),
    $fees (["author-content" => amount]), $prescreeningFee, $profile.
--}}
@php
    $user = auth()->user();
    $details = App\Http\Requests\ManuscriptSubmissionRequest::authorDetails(null, $user->id);
    $categoryName = $details['author_category_id'] ? collect($authorCategories)->get($details['author_category_id']) : null;
    $missing = array_keys(array_filter([
        'author category' => ! $categoryName,
        'institution' => blank($details['institution']),
    ]));
    $profileUrl = route('account.profile.edit', ['next' => route('submit', absolute: false).'#submission-form']);

    $steps = ['Manuscript', 'Declarations', 'Payment & submit'];
    $errorStep = match (true) {
        $errors->hasAny(['title', 'content_category_id', 'manuscript', 'keywords', 'abstract', 'co_authors.*']) => 0,
        $errors->hasAny(array_keys(App\Models\ManuscriptSubmission::DECLARATIONS)) => 1,
        default => 0,
    };
    $themes = $contentCategories->mapWithKeys(fn ($c) => [$c->id => $c->currentTheme?->fullLabel()])->filter();
    $initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('');
@endphp

@if ($missing)
    {{-- The profile must hold the author details before a manuscript can be submitted. --}}
    <div class="flex flex-wrap items-center justify-between gap-5 border border-border bg-card p-6 md:p-8">
        <div class="flex items-start gap-4">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center bg-secondary text-primary"><x-icon name="square-user" class="h-5 w-5" /></span>
            <div>
                <p class="font-display text-xl">Complete your author profile first</p>
                <p class="mt-1 text-muted-foreground">Your submission uses the details in your profile. Please add your {{ implode(' and ', $missing) }}, then come back here.</p>
            </div>
        </div>
        <x-button :href="$profileUrl" variant="primary" icon="pencil">Complete profile</x-button>
    </div>
@else
    <form method="POST" action="{{ route('account.submissions.store') }}" enctype="multipart/form-data" data-steps @go-to-step="step = $event.detail" class="border border-border bg-card"
        x-data="{
            step: {{ $errorStep }},
            author: @js((string) $details['author_category_id']),
            content: @js((string) old('content_category_id')),
            fees: @js($fees),
            themes: @js($themes),
            get fee() { return this.fees[this.author + '-' + this.content]; },
        }">
        @csrf

        {{-- Submitting as (from the profile) --}}
        <div class="flex flex-wrap items-center gap-x-4 gap-y-3 border-b border-border bg-secondary/60 px-6 py-4 md:px-8">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">{{ $initials }}</span>
            <div class="min-w-0 flex-1">
                <p class="label-caps text-[0.65rem] text-muted-foreground">Submitting as</p>
                <p class="font-medium">{{ $user->name }}</p>
                <p class="text-sm text-muted-foreground">{{ collect([$categoryName, $details['institution'], $details['country']])->filter()->implode(' · ') }}</p>
            </div>
            <a href="{{ $profileUrl }}" class="flex items-center gap-1 text-sm text-primary hover:underline"><x-icon name="pencil" class="h-3.5 w-3.5" /> Edit profile</a>
        </div>

        <ol class="grid grid-cols-3 border-b border-border">
            @foreach ($steps as $i => $label)
                <li>
                    <button type="button" @click="step = {{ $i }}" class="w-full border-b-2 px-4 py-4 text-left" :class="step === {{ $i }} ? 'border-primary' : 'border-transparent'">
                        <span class="label-caps block text-xs text-primary">Step {{ $i + 1 }}</span>
                        <span class="text-sm sm:text-base">{{ $label }}</span>
                    </button>
                </li>
            @endforeach
        </ol>

        <div class="p-6 md:p-8">
            <div x-show="step === 0" data-step="0" class="space-y-5" x-data="docxWordCount()">
                <div class="grid gap-5 md:grid-cols-2">
                    <x-form.input name="title" label="Title" required class="md:col-span-2" />
                    <x-form.field label="Content category" name="content_category_id" required>
                        <select name="content_category_id" x-model="content" class="field-input" required>
                            <option value="">Select a content category</option>
                            @foreach ($contentCategories as $category)<option value="{{ $category->id }}">{{ $category->name }} ({{ $category->wordLimitLabel() }})</option>@endforeach
                        </select>
                    </x-form.field>
                    <x-form.input name="keywords" label="Keywords" placeholder="constitutional law, federalism, taxation" hint="3 to 6 keywords, separated by commas." required />
                </div>
                <p x-show="themes[content]" x-cloak class="border-l-2 border-gold/60 bg-secondary px-4 py-3 text-sm">
                    This month's theme: <strong x-text="themes[content]"></strong>. Your manuscript should address this theme.
                </p>
                <div class="grid gap-5 md:grid-cols-2">
                    <x-form.field label="Manuscript (.docx)" name="manuscript" required hint="Word (.docx) only, up to 20 MB. We count the words automatically.">
                        <input type="file" name="manuscript" accept=".docx" class="field-input" required @change="count($event, '#auto_word_count')">
                        <p x-show="counting" class="text-sm text-muted-foreground">Counting words…</p>
                        <p x-show="error" x-text="error" class="text-sm text-destructive"></p>
                    </x-form.field>
                    <x-form.field label="Word count" hint="Filled automatically from your document.">
                        <input id="auto_word_count" type="number" class="field-input" readonly placeholder="Upload your manuscript">
                    </x-form.field>
                </div>
                <x-form.textarea name="abstract" label="Abstract" rows="6" required hint="Not more than 250 words." />
                @include('submissions._co-authors', ['submission' => new App\Models\ManuscriptSubmission])
            </div>

            <div x-show="step === 1" data-step="1" x-cloak class="space-y-4">
                @foreach (App\Models\ManuscriptSubmission::DECLARATIONS as $field => $label)
                    <x-form.checkbox :name="$field" :label="$label" required />
                @endforeach
            </div>

            <div x-show="step === 2" data-step="2" x-cloak class="space-y-4">
                <div class="border border-border bg-background p-5">
                    <div class="flex justify-between"><span>Plagiarism pre-screening fee (payable now)</span><strong>{{ money($prescreeningFee) }}</strong></div>
                    <div class="mt-2 flex justify-between text-muted-foreground">
                        <span>Publication fee (only if accepted)</span>
                        <span x-text="fee !== undefined ? @js(settings('payment.currency_symbol')) + Number(fee).toLocaleString('en-IN', { minimumFractionDigits: 2 }) : (content ? 'Not offered for this combination' : 'Select a content category')"></span>
                    </div>
                    <p class="mt-3 text-sm text-muted-foreground">Tax is added for Indian billing addresses. The manuscript is screened for plagiarism once the pre-screening fee is paid.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-border px-6 py-5 md:px-8">
            <x-button type="button" @click="step = Math.max(0, step - 1)" x-bind:disabled="step === 0" icon="arrow-left">Back</x-button>
            <x-button type="button" variant="primary" x-show="step < 2" @click="if (window.validateWithin($root.querySelectorAll('[data-step]')[step])) step++">Next</x-button>
            <x-button type="submit" variant="primary" icon="send" x-show="step === 2" x-cloak>Submit manuscript</x-button>
        </div>
    </form>
@endif
