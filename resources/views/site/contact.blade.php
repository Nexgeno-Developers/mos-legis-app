<x-layouts.site :title="$page?->seo_title ?: 'Contact'" :description="$page?->seo_description">
    <x-page-header eyebrow="Contact" :title="$page?->title ?: 'Write to the Editorial Desk'" :intro="$page?->excerpt ?: 'Questions on submissions, review timelines, patronage or permissions reach a member of the editorial team directly.'" />
    <div class="mx-auto grid max-w-[1200px] gap-12 px-6 py-16 lg:grid-cols-[1.3fr_1fr]">
        <section>
            <x-section-heading eyebrow="Enquiry Form" title="Send Us a Message" />
            <form method="POST" action="{{ route('contact.store') }}" class="mt-8 space-y-5">
                @csrf
                <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
                <div class="grid gap-5 md:grid-cols-2">
                    <x-form.input name="name" label="Name" :value="auth()->user()?->name" required />
                    <x-form.input name="email" type="email" label="Email" :value="auth()->user()?->email" required />
                    <x-form.select name="purpose" label="Purpose of enquiry" :options="array_combine($purposes, $purposes)" placeholder="Select" required />
                    <x-form.input name="submission_id" label="Submission ID (if any)" placeholder="MOS-00042" />
                </div>
                <x-form.textarea name="message" label="Message" rows="6" required />
                <x-button type="submit" variant="primary" icon="send">Send message</x-button>
            </form>
        </section>
        <aside class="space-y-10">
            <section>
                <x-section-heading eyebrow="Direct Lines" title="Editorial Contacts" />
                <x-dl class="mt-6 sm:grid-cols-1!" :items="[
                    'Chief editor' => $page?->meta('chief_editor_email') ? '<a class=\'text-primary hover:underline\' href=\'mailto:'.e($page->meta('chief_editor_email')).'\'>'.e($page->meta('chief_editor_email')).'</a>' : null,
                    'General queries' => $page?->meta('general_query_email') ? '<a class=\'text-primary hover:underline\' href=\'mailto:'.e($page->meta('general_query_email')).'\'>'.e($page->meta('general_query_email')).'</a>' : null,
                    'Telephone' => e($page?->meta('telephone')),
                    'Registered office' => nl2br(e($page?->meta('office_address'))),
                    'Desk hours' => e($page?->meta('desk_hours')),
                ]" />
            </section>
            @if ($page?->meta('faqs'))
                <section>
                    <x-section-heading eyebrow="Before You Write" title="Common Questions" />
                    <div class="mt-6 divide-y divide-border border border-border bg-card">
                        @foreach ($page->meta('faqs') as $faq)
                            <details class="group px-5 py-4">
                                <summary class="flex cursor-pointer items-center justify-between gap-3 font-medium">{{ $faq['question'] }} <x-icon name="chevron-down" class="transition group-open:rotate-180" /></summary>
                                <p class="mt-2 text-muted-foreground">{{ $faq['answer'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endif
        </aside>
    </div>
</x-layouts.site>
