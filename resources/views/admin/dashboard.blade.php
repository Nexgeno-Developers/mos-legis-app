<x-layouts.admin title="Dashboard">
    <x-admin.heading title="Dashboard" description="A snapshot of users, submissions, payments, content and recent activity." />

    <div class="mt-8 space-y-8">
        @if ($submissions)
            <section>
                <h2 class="label-caps mb-3 text-sm text-muted-foreground">Manuscript submissions</h2>
                <div class="grid gap-px border border-border bg-border sm:grid-cols-2 lg:grid-cols-5">
                    <x-stat-card label="Total" :value="number_format($submissions['total'])" :href="route('admin.submissions.index')" />
                    <x-stat-card label="Pending" :value="number_format($submissions['pending'])" :href="route('admin.submissions.index', ['stage' => 'pending'])" />
                    <x-stat-card label="In review" :value="number_format($submissions['in_review'])" :href="route('admin.submissions.index', ['stage' => 'in_review'])" />
                    <x-stat-card label="Approved" :value="number_format($submissions['approved'])" :href="route('admin.submissions.index', ['stage' => 'approved'])" />
                    <x-stat-card label="Published" :value="number_format($submissions['published'])" :href="route('admin.submissions.index', ['stage' => 'published'])" />
                </div>
            </section>
        @endif

        {{-- Two columns only when both sections are shown; one section takes the full width. --}}
        <div @class(['grid gap-8', 'lg:grid-cols-2' => $users && $payments])>
            @if ($users)
                <section>
                    <h2 class="label-caps mb-3 text-sm text-muted-foreground">Users</h2>
                    {{-- One figure per card (no extra hint line), so the cards line up with the Payments cards. --}}
                    <div class="grid gap-px border border-border bg-border sm:grid-cols-3">
                        <x-stat-card label="Superadmins" :value="number_format($users['admins'] - $users['reviewers'])" :href="route('admin.users.index', ['role' => 'superadmin'])" />
                        <x-stat-card label="Reviewers" :value="number_format($users['reviewers'])" :href="route('admin.users.index', ['role' => 'reviewer'])" />
                        <x-stat-card label="Authors" :value="number_format($users['authors'])" :href="route('admin.users.index', ['role' => 'author'])" />
                    </div>
                </section>
            @endif
            @if ($payments)
                <section>
                    <h2 class="label-caps mb-3 text-sm text-muted-foreground">Payments</h2>
                    <div class="grid gap-px border border-border bg-border sm:grid-cols-2">
                        <x-stat-card label="Total payments" :value="money($payments['amount'])" :href="route('admin.payments.index')" />
                        <x-stat-card label="Successful transactions" :value="number_format($payments['count'])" />
                    </div>
                </section>
            @endif
        </div>

        @if ($blogs)
            <section>
                <h2 class="label-caps mb-3 text-sm text-muted-foreground">Blogs</h2>
                <div class="grid gap-px border border-border bg-border sm:grid-cols-3 lg:grid-cols-6">
                    <x-stat-card label="Total blogs" :value="$blogs['total']" />
                    <x-stat-card label="Active blogs" :value="$blogs['active']" />
                    <x-stat-card label="Inactive blogs" :value="$blogs['inactive']" />
                    <x-stat-card label="Blog comments" :value="$blogs['comments']" />
                    <x-stat-card label="Approved comments" :value="$blogs['approved_comments']" />
                    <x-stat-card label="Disapproved comments" :value="$blogs['disapproved_comments']" />
                </div>
            </section>
        @endif

        @if ($jobs)
            <section>
                <h2 class="label-caps mb-3 text-sm text-muted-foreground">Job postings</h2>
                <div class="grid gap-px border border-border bg-border sm:grid-cols-3">
                    <x-stat-card label="Total postings" :value="$jobs['total']" />
                    <x-stat-card label="Active postings" :value="$jobs['active']" />
                    <x-stat-card label="Expired postings" :value="$jobs['expired']" />
                </div>
            </section>
        @endif

        {{-- Side by side only when both tables are shown (a reviewer may see just one). --}}
        <div @class(['grid gap-8', 'xl:grid-cols-2' => auth()->user()->can('submissions.view') && auth()->user()->can('activity-logs.view')])>
            @can('submissions.view')
                <section>
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="font-display text-2xl text-foreground">Recent submissions</h2>
                        <a href="{{ route('admin.submissions.index') }}" class="text-sm text-primary hover:underline">View all</a>
                    </div>
                    <x-table :columns="['ID', 'Title', 'Stage', 'Waiting']" :rows="$recentSubmissions" empty="No submissions yet.">
                        @foreach ($recentSubmissions as $submission)
                            <tr>
                                <td class="font-mono text-sm whitespace-nowrap">{{ $submission->reference() }}</td>
                                <td><a href="{{ route('admin.submissions.show', $submission) }}" class="hover:text-primary">{{ Str::limit($submission->title, 60) }}</a></td>
                                <td><x-status-badge :status="$submission->stage" /></td>
                                <td class="whitespace-nowrap text-sm text-muted-foreground">{{ $submission->waitingSince()->diffForHumans(short: true) }}</td>
                            </tr>
                        @endforeach
                    </x-table>
                </section>
            @endcan

            @can('activity-logs.view')
                <section>
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="font-display text-2xl text-foreground">Recent activity</h2>
                        <a href="{{ route('admin.activity-logs.index') }}" class="text-sm text-primary hover:underline">View all</a>
                    </div>
                    <x-table :columns="['User', 'Action', 'Date']" :rows="$recentActivity" empty="No activity yet.">
                        @foreach ($recentActivity as $log)
                            <tr>
                                <td class="whitespace-nowrap">{{ $log->user?->name ?? 'System' }}</td>
                                <td><span class="text-muted-foreground">{{ $log->module }}:</span> {{ $log->action }}</td>
                                <td class="whitespace-nowrap text-sm text-muted-foreground">{{ format_date($log->created_at, true) }}</td>
                            </tr>
                        @endforeach
                    </x-table>
                </section>
            @endcan
        </div>
    </div>
</x-layouts.admin>
