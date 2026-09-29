@php
    $stage = $submission->stage;
    $canReview = auth()->user()->can('review', $submission) && $stage->awaitsReviewerDecision();
@endphp
<x-layouts.admin :title="$submission->reference()">
    <x-admin.heading :title="$submission->reference()" :description="$submission->title">
        <x-slot:actions>
            @can('update', $submission)
                <x-button :href="route('admin.submissions.edit', $submission)" icon="pencil">Edit</x-button>
            @endcan
            <x-button :href="route('admin.submissions.index')" icon="arrow-left">Back</x-button>
        </x-slot:actions>
    </x-admin.heading>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <x-status-badge :status="$stage" class="text-sm!" />
        <span class="text-sm text-muted-foreground">In this stage for {{ $submission->waitingSince()->diffForHumans(syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE) }}</span>
        @foreach ($submission->awards as $award)
            <x-badge tone="gold"><x-icon name="award" class="h-3 w-3" /> Best Paper · {{ $award->periodLabel() }}</x-badge>
        @endforeach
    </div>

    <div class="mt-6 grid gap-8 xl:grid-cols-[1fr_24rem]">
        <div class="space-y-8">
            <x-admin.panel title="Manuscript information">
                <x-dl :items="[
                    'Title' => e($submission->title),
                    'Content category' => e($submission->contentCategory->name),
                    'Theme' => e($submission->theme?->fullLabel()),
                    'Word count' => number_format($submission->word_count).' <span class=\'text-sm text-muted-foreground\'>('.e($submission->contentCategory->wordLimitLabel()).')</span>',
                    'Keywords' => e(implode(', ', $submission->keywords ?? [])),
                    'Submitted' => format_date($submission->created_at, true),
                    'Plagiarism similarity' => $submission->plagiarism_similarity !== null ? number_format((float) $submission->plagiarism_similarity, 2).'%' : null,
                    'Published' => $submission->published_at ? format_date($submission->published_at) : null,
                ]" />
                <div class="mt-6">
                    <p class="label-caps text-xs text-muted-foreground">Abstract</p>
                    <p class="mt-1 whitespace-pre-line text-base">{{ $submission->abstract }}</p>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-button size="sm" icon="download" :href="route('admin.submissions.download', $submission)">Download manuscript (.docx)</x-button>
                    @if ($submission->certificate)
                        <x-button size="sm" icon="award" :href="route('admin.submissions.certificate', $submission)">Certificate {{ $submission->certificate->certificate_number }}</x-button>
                    @endif
                </div>
            </x-admin.panel>

            <x-admin.panel title="Author details">
                <x-dl :items="[
                    'Author' => e($submission->author->name).'<br><span class=\'text-sm text-muted-foreground\'>'.e($submission->author->email).'</span>',
                    'Author category' => e($submission->authorCategory->name),
                    'Institution' => e($submission->institution),
                    'Country' => e($submission->country),
                    'Co-authors' => e(implode(', ', $submission->co_authors ?? [])),
                    'ORCID' => e($submission->author->authorProfile?->orcid),
                ]" />
            </x-admin.panel>

            <x-admin.panel title="Review history">
                @forelse ($submission->revisions as $revision)
                    <div class="border-l-2 border-gold/60 py-1 pl-4 [&:not(:first-child)]:mt-5">
                        <p class="text-sm text-muted-foreground">Round {{ $revision->round }} · {{ format_date($revision->decided_at, true) }} · {{ $revision->reviewer?->name ?? 'Reviewer' }}</p>
                        <p class="mt-1"><x-status-badge :status="$revision->decision" /></p>
                        @if ($revision->reviewer_remarks)<p class="mt-2 whitespace-pre-line text-base">{{ $revision->reviewer_remarks }}</p>@endif
                        <div class="mt-2 flex flex-wrap gap-4 text-sm">
                            <a href="{{ route('admin.submissions.revision-download', [$submission, $revision, 'reviewed']) }}" class="text-primary hover:underline">Reviewed file</a>
                            @if ($revision->resubmitted_attachment)
                                <a href="{{ route('admin.submissions.revision-download', [$submission, $revision, 'resubmitted']) }}" class="text-primary hover:underline">Resubmitted file ({{ number_format($revision->resubmitted_word_count) }} words, {{ format_date($revision->resubmitted_at) }})</a>
                            @endif
                        </div>
                        @if ($revision->author_response)<p class="mt-2 text-sm"><span class="text-muted-foreground">Author response:</span> {{ $revision->author_response }}</p>@endif
                    </div>
                @empty
                    <p class="text-muted-foreground">No review decisions yet.</p>
                @endforelse
            </x-admin.panel>

            <x-admin.panel title="Payments">
                <x-table :columns="['ID', 'Purpose', 'Total', 'Status', 'Paid At', 'Invoice']" :rows="$submission->payments" empty="No payments yet." class="min-w-0">
                    @foreach ($submission->payments as $payment)
                        <tr>
                            <td class="font-mono text-sm">{{ $payment->id }}</td>
                            <td>{{ $payment->payment_purpose->label() }}</td>
                            <td>{{ money($payment->total_amount) }}</td>
                            <td><x-status-badge :status="$payment->payment_status" /></td>
                            <td class="text-sm">{{ format_date($payment->paid_at, true) }}</td>
                            <td>@can('payments.view')<a href="{{ route('admin.payments.invoice', $payment) }}" target="_blank" class="text-sm text-primary hover:underline">PDF</a>@endcan</td>
                        </tr>
                    @endforeach
                </x-table>
                @if ($publicationFee !== null)
                    <p class="mt-3 text-sm text-muted-foreground">Publication fee for this combination: {{ money($publicationFee) }} (+ tax for Indian billing addresses).</p>
                @endif
            </x-admin.panel>

            <x-admin.panel title="Plagiarism checks">
                <x-slot:actions>
                    @can('plagiarism-checks.recheck')
                        <form method="POST" action="{{ route('admin.submissions.recheck', $submission) }}">@csrf<x-button type="submit" size="sm" icon="refresh-cw">Re-check</x-button></form>
                    @endcan
                </x-slot:actions>
                <x-table :columns="['ID', 'Status', 'Similarity', 'Checked At', 'Report']" :rows="$submission->plagiarismChecks" empty="No checks yet — the check runs after the pre-screening fee is paid." class="min-w-0">
                    @foreach ($submission->plagiarismChecks->sortByDesc('id') as $check)
                        <tr>
                            <td class="font-mono text-sm">@can('plagiarism-checks.view')<a class="hover:text-primary" href="{{ route('admin.plagiarism-checks.show', $check) }}">{{ $check->id }}</a>@else{{ $check->id }}@endcan</td>
                            <td><x-status-badge :status="$check->check_status" /></td>
                            <td><x-similarity :value="$check->similarity_percentage" :threshold="$threshold" /></td>
                            <td class="text-sm">{{ format_date($check->checked_at, true) }}</td>
                            <td>@if ($check->report_file && auth()->user()->can('plagiarism-checks.view'))<a href="{{ route('admin.plagiarism-checks.report', $check) }}" class="text-sm text-primary hover:underline">PDF</a>@endif</td>
                        </tr>
                    @endforeach
                </x-table>
            </x-admin.panel>
        </div>

        <div class="space-y-8">
            @if ($canReview)
                <x-admin.panel title="Review decision" description="Approve, request a revision or reject. Remarks are shared with the author.">
                    <form method="POST" action="{{ route('admin.submissions.decide', $submission) }}" class="space-y-4" x-data="{ decision: @js(old('decision', 'approved')) }">
                        @csrf
                        <x-form.field label="Decision" name="decision" required>
                            <select name="decision" x-model="decision" class="field-input">
                                @foreach (App\Enums\RevisionDecision::options() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-form.field>
                        <x-form.textarea name="reviewer_remarks" label="Remarks for the author" rows="5" x-bind:required="decision !== 'approved'" />
                        <x-button type="submit" variant="primary" icon="gavel" class="w-full">Record decision</x-button>
                    </form>
                </x-admin.panel>
            @endif

            @can('assign', $submission)
                <x-admin.panel title="Reviewer" :description="$submission->reviewer ? 'Assigned '.format_date($submission->assigned_at, true) : 'Not assigned'">
                    @if ($submission->reviewer)
                        <p class="mb-4 text-base"><strong>{{ $submission->reviewer->name }}</strong><br><span class="text-sm text-muted-foreground">{{ $submission->reviewer->email }}</span></p>
                    @endif
                    @if (in_array($stage, [App\Enums\ManuscriptStage::PlagiarismAccepted, App\Enums\ManuscriptStage::InReview, App\Enums\ManuscriptStage::Revision, App\Enums\ManuscriptStage::Resubmitted], true))
                        <form method="POST" action="{{ route('admin.submissions.assign', $submission) }}" class="space-y-3">
                            @csrf
                            <x-form.select name="reviewer_id" label="Eligible reviewers" placeholder="Select a reviewer"
                                :options="$eligibleReviewers->mapWithKeys(fn ($r) => [$r->id => $r->name.' — '.$r->active_assignments_count.' active'])" />
                            <div class="flex gap-2">
                                <x-button type="submit" variant="primary" icon="user-check">{{ $submission->reviewer ? 'Reassign' : 'Assign' }}</x-button>
                                <x-button type="submit" name="auto" value="1" icon="shuffle" formnovalidate>Auto-assign</x-button>
                            </div>
                            @if ($eligibleReviewers->isEmpty())<p class="text-sm text-warning">No active reviewer covers {{ $submission->contentCategory->name }}.</p>@endif
                        </form>
                    @else
                        <p class="text-sm text-muted-foreground">Reviewers are assigned after the manuscript passes plagiarism screening.</p>
                    @endif
                </x-admin.panel>
            @endcan

            @can('changeStage', $submission)
                <x-admin.panel title="Change stage" description="Manual override; the author and reviewer are notified.">
                    <form method="POST" action="{{ route('admin.submissions.change-stage', $submission) }}" class="space-y-3" onsubmit="return confirm('Change the stage of this manuscript?')">
                        @csrf @method('PATCH')
                        <x-form.select name="stage" label="Stage" :options="App\Enums\ManuscriptStage::options()" :value="$stage" />
                        <x-form.input name="remarks" label="Remarks" />
                        <x-button type="submit" icon="git-branch">Change stage</x-button>
                    </form>
                </x-admin.panel>
            @endcan

            @can('awardBestPaper', $submission)
                @if ($stage === App\Enums\ManuscriptStage::Published)
                    <x-admin.panel title="Mark as Best Paper Winner" description="One winner per monthly or quarterly period.">
                        @foreach ($submission->awards as $award)
                            <div class="mb-4 flex items-center justify-between border border-gold/50 px-3 py-2 text-sm">
                                <span>{{ $award->periodLabel() }} · {{ money($award->prize_amount) }}</span>
                                <x-delete-button :action="route('admin.submissions.awards.destroy', [$submission, $award])" label="Remove" icon="x" confirm="Remove this award?" />
                            </div>
                        @endforeach
                        <form method="POST" action="{{ route('admin.submissions.awards.store', $submission) }}" class="space-y-3" x-data="{ type: @js(old('period_type', 'monthly')) }">
                            @csrf
                            <x-form.field label="Award period type" name="period_type" required>
                                <select name="period_type" x-model="type" class="field-input">
                                    @foreach (App\Enums\AwardPeriodType::options() as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                                </select>
                            </x-form.field>
                            <div class="grid grid-cols-2 gap-3">
                                <div x-show="type === 'monthly'"><x-form.select name="award_month" label="Month" :options="array_combine(App\Models\BestPaperAward::MONTHS, App\Models\BestPaperAward::MONTHS)" :value="now()->subMonth()->format('F')" /></div>
                                <div x-show="type === 'quarterly'" x-cloak><x-form.select name="award_quarter" label="Quarter" :options="array_combine(App\Models\BestPaperAward::QUARTERS, App\Models\BestPaperAward::QUARTERS)" /></div>
                                <x-form.input name="award_year" type="number" label="Year" :value="now()->year" />
                            </div>
                            <x-form.input name="prize_amount" type="number" step="0.01" label="Prize amount (₹)" value="2000" />
                            <x-form.textarea name="editorial_citation" label="Editorial citation" rows="3" required />
                            <x-button type="submit" variant="gold" icon="award" class="w-full">Mark as winner</x-button>
                        </form>
                    </x-admin.panel>
                @endif
            @endcan

            @can('delete', $submission)
                <x-admin.panel title="Danger zone">
                    <x-delete-button :action="route('admin.submissions.destroy', $submission)" label="Delete manuscript" confirm="Delete this manuscript and its files? This cannot be undone." />
                </x-admin.panel>
            @endcan
        </div>
    </div>
</x-layouts.admin>
