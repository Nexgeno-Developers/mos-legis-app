@php
    $address = $page?->meta('office_address');
    // The map appears only when one is set in Admin → Pages → Contact.
    $mapUrl = App\Support\GoogleMap::embedUrl($page?->meta('map_embed_url'));
    $directions = App\Support\GoogleMap::directionsUrl($address);
    $faqs = $page?->meta('faqs', []) ?: [];
    $generalEmail = $page?->meta('general_query_email');
    $contacts = array_filter([
        ['mail', 'Chief editor', $page?->meta('chief_editor_email'), 'mailto:'],
        ['inbox', 'General queries', $generalEmail, 'mailto:'],
        ['phone', 'Telephone', $page?->meta('telephone'), 'tel:'],
        ['clock', 'Desk hours', $page?->meta('desk_hours'), null],
    ], fn ($c) => filled($c[2]));
@endphp
<x-layouts.site :title="$page?->seo_title ?: 'Contact'" :description="$page?->seo_description">
    <x-page-header :title="$page?->title ?: 'Write to the Editorial Desk'" :intro="$page?->excerpt ?: 'Questions on submissions, review timelines, patronage or permissions reach a member of the editorial team directly.'" />

    <div class="mx-auto max-w-[1200px] space-y-12 px-4 py-8 sm:px-6 md:space-y-16 md:py-10">

        {{-- 1. Form + contact details (equal height) --}}
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] lg:gap-8">
            <section id="enquiry" class="flex flex-col border border-border bg-card p-6 md:p-8">
                <h2 class="font-display text-2xl">Send us a message</h2>
                <p class="mt-1 text-sm text-muted-foreground">Fill in the form and the right desk will reply by email, usually within two working days.</p>
                <form method="POST" action="{{ route('contact.store') }}" class="mt-6 flex flex-1 flex-col gap-5">
                    @csrf
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <div class="grid gap-5 md:grid-cols-2">
                        <x-form.input name="name" label="Name" :value="auth()->user()?->name" required autocomplete="name" />
                        <x-form.input name="email" type="email" label="Email" :value="auth()->user()?->email" required autocomplete="email" />
                        <x-form.phone label="Phone" :value="auth()->user()?->phone" />
                        <x-form.select name="purpose" label="Purpose of enquiry" :options="array_combine($purposes, $purposes)" placeholder="Select" required />
                    </div>
                    <x-form.textarea name="message" label="Message" rows="5" required class="flex-1 [&_textarea]:h-full [&_textarea]:min-h-32" />
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-xs text-muted-foreground">Fields marked <span class="text-primary">*</span> are required.</p>
                        <x-button type="submit" variant="primary" icon="send">Send message</x-button>
                    </div>
                </form>
            </section>

            <aside class="flex flex-col border border-border bg-card">
                <div class="border-b border-border p-6">
                    <h2 class="font-display text-2xl">Editorial contacts</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Prefer email? Write to us directly.</p>
                </div>
                <ul class="flex-1 divide-y divide-border">
                    @foreach ($contacts as [$icon, $label, $value, $scheme])
                        <li class="flex items-center gap-4 px-6 py-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center bg-secondary text-primary"><x-icon :name="$icon" class="h-[18px] w-[18px]" /></span>
                            <span class="min-w-0">
                                <span class="label-caps block text-[0.65rem] text-muted-foreground">{{ $label }}</span>
                                @if ($scheme)
                                    <a href="{{ $scheme.preg_replace('/\s+/', '', $value) }}" class="block break-words font-medium text-foreground hover:text-primary">{{ $value }}</a>
                                @else
                                    <span class="block font-medium">{{ $value }}</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                    @if ($address)
                        <li class="flex items-start gap-4 px-6 py-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center bg-secondary text-primary"><x-icon name="map-pin" class="h-[18px] w-[18px]" /></span>
                            <span class="min-w-0">
                                <span class="label-caps block text-[0.65rem] text-muted-foreground">Registered office</span>
                                <span class="block font-medium">{!! nl2br(e($address)) !!}</span>
                            </span>
                        </li>
                    @endif
                </ul>
            </aside>
        </div>

        {{-- 2. Full-width map with address card --}}
        @if ($mapUrl)
            <section class="relative overflow-hidden border border-border bg-card" aria-label="Location map">
                <iframe src="{{ $mapUrl }}" title="Map: {{ settings('general.application_name') }} editorial office"
                    class="block h-80 w-full border-0 md:h-[26rem]" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                @if ($address)
                    <div class="border-t border-border bg-card p-5 md:absolute md:top-6 md:right-6 md:w-80 md:border md:shadow-lg">
                        <p class="label-caps text-[0.65rem] text-primary">Visit us</p>
                        <p class="mt-1 font-display text-lg leading-snug">{{ settings('general.application_name') }} Editorial Office</p>
                        <p class="mt-2 text-sm text-muted-foreground">{!! nl2br(e($address)) !!}</p>
                        @if ($page?->meta('desk_hours'))<p class="mt-2 text-sm"><span class="text-muted-foreground">Open:</span> {{ $page->meta('desk_hours') }}</p>@endif
                        @if ($directions)
                            <x-button :href="$directions" target="_blank" rel="noopener" variant="primary" size="sm" icon="map-pin" class="mt-4">Get directions</x-button>
                        @endif
                    </div>
                @endif
            </section>
        @endif

        {{-- 3. FAQ --}}
        @if ($faqs)
            <section class="grid gap-8 border-t border-border pt-12 md:pt-16 lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)] lg:gap-12">
                <div class="lg:sticky lg:top-40 lg:self-start">
                    <p class="label-caps text-sm text-primary">Before you write</p>
                    <h2 class="mt-2 font-display text-3xl">Common questions</h2>
                    <p class="mt-3 text-muted-foreground">Quick answers about submissions, review timelines and fees.</p>
                    <div class="mt-6 border border-border bg-secondary/60 p-5">
                        <p class="font-semibold">Still have a question?</p>
                        <p class="mt-1 text-sm text-muted-foreground">Our editorial desk is happy to help.</p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <x-button href="#enquiry" variant="primary" size="sm" icon="send">Send a message</x-button>
                            @if ($generalEmail)<x-button :href="'mailto:'.$generalEmail" size="sm" icon="mail">Email us</x-button>@endif
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-border border-y border-border" x-data="{ active: 0 }">
                    @foreach ($faqs as $i => $faq)
                        <div>
                            <h3>
                                <button type="button" @click="active = active === {{ $i }} ? null : {{ $i }}" :aria-expanded="active === {{ $i }}" aria-controls="faq-{{ $i }}"
                                    class="flex w-full items-center justify-between gap-6 py-5 text-left text-lg font-semibold transition-colors hover:text-primary"
                                    :class="active === {{ $i }} && 'text-primary'">
                                    <span>{{ $faq['question'] }}</span>
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border transition-colors"
                                        :class="active === {{ $i }} ? 'border-primary bg-primary text-primary-foreground' : 'border-border text-muted-foreground'">
                                        <span class="flex transition-transform duration-200" :class="active === {{ $i }} && 'rotate-45'"><x-icon name="plus" class="h-4 w-4" /></span>
                                    </span>
                                </button>
                            </h3>
                            <div id="faq-{{ $i }}" x-show="active === {{ $i }}" x-collapse.duration.200ms @if ($i !== 0) x-cloak @endif>
                                <p class="measure pr-14 pb-5 text-muted-foreground">{{ $faq['answer'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.site>
