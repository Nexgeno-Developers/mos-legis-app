{{-- Job posting fields shared by the admin panel and the author portal. Expects $job.
     The job is published on the day it is posted and listed until its application deadline. --}}
<div class="space-y-8">
    <x-admin.panel title="Basic information">
        <div class="grid gap-5 md:grid-cols-2">
            <x-form.input name="job_title" label="Job title" :value="$job->job_title" required />
            <x-form.input name="organisation" label="Organisation" :value="$job->organisation" required />
            <x-form.input name="location" label="Location" :value="$job->location" required />
            <x-form.select name="work_mode" label="Work mode" :options="App\Enums\WorkMode::options()" :value="$job->work_mode" placeholder="Select" required />
            <x-form.select name="employment_type" label="Employment type" :options="App\Enums\EmploymentType::options()" :value="$job->employment_type" placeholder="Select" required />
            <x-form.input name="experience" label="Experience" placeholder="2–4 years" :value="$job->experience" required />
            <x-form.input name="practice_area" label="Practice area" placeholder="Corporate, Litigation, Arbitration…" :value="$job->practice_area" required />
            <x-form.input name="salary" label="Salary (optional)" :value="$job->salary" />
        </div>
    </x-admin.panel>

    <x-admin.panel title="Job description">
        <div class="space-y-5">
            <x-form.textarea name="summary" label="Summary" :value="$job->summary" rows="3" required />
            <x-form.textarea name="responsibilities" label="Responsibilities (optional)" :value="$job->responsibilities" rows="4" />
            <x-form.textarea name="qualifications" label="Qualifications (optional)" :value="$job->qualifications" rows="3" />
            <x-form.textarea name="required_skills" label="Required skills (optional)" :value="$job->required_skills" rows="2" />
        </div>
    </x-admin.panel>

    <x-admin.panel title="How to apply">
        <div class="grid gap-5 md:grid-cols-2">
            <x-form.input name="source_url" type="url" label="Source URL" :value="$job->source_url" required class="md:col-span-2"
                placeholder="https://…" hint="The public “Apply now” button links here." />
            <x-form.input name="application_deadline" type="date" label="Application deadline" :value="$job->application_deadline?->toDateString()" required
                hint="The listing is hidden from the website after this date." />
            <x-form.input name="source_name" label="Source name (optional)" :value="$job->source_name" placeholder="e.g. Firm website, LinkedIn" />
            <x-form.select name="status" label="Status" :options="App\Enums\RecordStatus::options()" :value="$job->status ?? 'Active'" required />
        </div>
    </x-admin.panel>
</div>
