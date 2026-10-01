<x-layouts.admin :title="'Enquiry · '.$enquiry->name">
    <x-admin.heading :title="$enquiry->name" :description="ucfirst($enquiry->form_name->value).' form · received '.format_date($enquiry->created_at, true)">
        <x-slot:actions>
            <x-button :href="route('admin.enquiries.index', ['form' => $enquiry->form_name->value])" icon="arrow-left">Back</x-button>
        </x-slot:actions>
    </x-admin.heading>

    <x-admin.panel class="mx-auto mt-8 max-w-3xl">
        <x-dl :items="[
            'Name' => e($enquiry->name),
            'Email' => '<a class=\'text-primary hover:underline\' href=\'mailto:'.e($enquiry->email).'\'>'.e($enquiry->email).'</a>',
            'Phone' => e(App\Support\PhoneNumbers::display($enquiry->phone)),
            'IP address' => e($enquiry->ip),
        ]" />

        <div class="mt-8 border-t border-border pt-6">
            <h2 class="label-caps text-sm text-primary">Other details</h2>
            <dl class="mt-3 space-y-4">
                @foreach ((array) $enquiry->form_data as $key => $value)
                    @continue($key === 'resume_path')
                    <div>
                        <dt class="label-caps text-xs text-muted-foreground">{{ Str::headline($key) }}</dt>
                        <dd class="mt-0.5 whitespace-pre-line text-base">{{ is_scalar($value) && $value !== '' ? $value : '—' }}</dd>
                    </div>
                @endforeach
                @if (! empty($enquiry->form_data['resume_path']))
                    <div>
                        <dt class="label-caps text-xs text-muted-foreground">Resume / CV</dt>
                        <dd class="mt-1"><x-button size="sm" icon="download" :href="route('admin.enquiries.resume', $enquiry)">Download CV</x-button></dd>
                    </div>
                @endif
            </dl>
        </div>

        @can('enquiries.delete')
            <div class="mt-8 border-t border-border pt-6"><x-delete-button :action="route('admin.enquiries.destroy', $enquiry)" label="Delete enquiry" /></div>
        @endcan
    </x-admin.panel>
</x-layouts.admin>
