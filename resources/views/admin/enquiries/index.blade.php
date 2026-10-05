<x-layouts.admin title="Enquiries">
    <x-admin.heading title="Enquiries" description="Career applications and contact-form submissions from the public site, in one queue.">
        <x-slot:actions>
            @foreach (App\Enums\EnquiryForm::cases() as $form)
                <x-button :href="route('admin.enquiries.index', ['form' => $form->value])" :variant="request('form') === $form->value ? 'primary' : 'outline'" :icon="$form->value === 'career' ? 'briefcase' : 'mail'">
                    {{ ucfirst($form->value) }} ({{ $counts[$form->value] ?? 0 }})
                </x-button>
            @endforeach
        </x-slot:actions>
    </x-admin.heading>

    <x-filter-bar>
        @if (request('form'))<input type="hidden" name="form" value="{{ request('form') }}">@endif
        <x-filter.search placeholder="Search name, email, phone, details…" />
        <x-filter.date-range label="Received" />
    </x-filter-bar>

    <x-table :columns="['Name', 'Email', 'Phone', 'Type', 'Details', 'Received', 'Actions']" :rows="$enquiries">
        @foreach ($enquiries as $enquiry)
            <tr>
                <td class="font-medium">{{ $enquiry->name }}</td>
                <td class="text-sm"><a href="mailto:{{ $enquiry->email }}" class="hover:text-primary">{{ $enquiry->email }}</a></td>
                <td class="text-sm">{{ App\Support\PhoneNumbers::display($enquiry->phone) ?? '—' }}</td>
                <td><x-badge :tone="$enquiry->form_name->value === 'career' ? 'gold' : 'info'">{{ $enquiry->form_name->value }}</x-badge></td>
                <td class="max-w-sm text-sm text-muted-foreground">
                    {{ Str::limit($enquiry->form_data['purpose'] ?? $enquiry->form_data['position'] ?? '', 40) }}
                    @if (! empty($enquiry->form_data['message'])) — {{ Str::limit($enquiry->form_data['message'], 70) }}@endif
                </td>
                <td class="whitespace-nowrap text-sm text-muted-foreground">{{ format_date($enquiry->created_at, true) }}</td>
                <td>
                    <x-row-actions>
                        <x-action-link :href="route('admin.enquiries.show', $enquiry)" icon="eye">View</x-action-link>
                        @can('enquiries.delete')
                            <x-delete-button :action="route('admin.enquiries.destroy', $enquiry)" />
                        @endcan
                    </x-row-actions>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $enquiries->links() }}
</x-layouts.admin>
