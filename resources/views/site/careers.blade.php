<x-layouts.site :title="$page?->seo_title ?: 'Careers'" :description="$page?->seo_description">
    <x-page-header eyebrow="Careers" :title="$page?->title ?: 'Careers at MOS Legis'" :intro="$page?->excerpt" />
    <div class="mx-auto grid max-w-[1200px] gap-12 px-4 py-8 sm:px-6 md:py-10 lg:grid-cols-[1fr_1fr]">
        <section>
            @if ($page?->content)<div class="prose-legis">{!! $page->content !!}</div>@endif
            @if ($page?->meta('apply_email'))<p class="mt-6 text-muted-foreground">Questions? Write to <a class="text-primary hover:underline" href="mailto:{{ $page->meta('apply_email') }}">{{ $page->meta('apply_email') }}</a>.</p>@endif
        </section>
        <section>
            <x-section-heading eyebrow="Apply" title="Application Form" />
            <form method="POST" action="{{ route('careers.store') }}" enctype="multipart/form-data" class="mt-8 space-y-5">
                @csrf
                <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
                <x-form.input name="name" label="Full name" required />
                <x-form.input name="email" type="email" label="Email" required />
                <x-form.input name="phone" label="Phone number" required />
                <x-form.input name="position" label="Position applying for" placeholder="General application" required />
                <x-form.field label="Resume / CV" name="resume" required hint="PDF, DOC or DOCX, up to 5 MB.">
                    <input type="file" name="resume" accept=".pdf,.doc,.docx" class="field-input" required>
                </x-form.field>
                <x-button type="submit" variant="primary" icon="send">Submit application</x-button>
            </form>
        </section>
    </div>
</x-layouts.site>
