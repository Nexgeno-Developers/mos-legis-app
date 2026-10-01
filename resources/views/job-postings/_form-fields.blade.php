{{-- Job posting fields shared by the admin panel and the author portal. Expects $job. --}}
<div class="space-y-8" x-data="{ method: @js(old('application_method', $job->application_method?->value ?? 'External URL')) }">
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
            <x-form.textarea name="responsibilities" label="Responsibilities" :value="$job->responsibilities" rows="4" required />
            <x-form.textarea name="qualifications" label="Qualifications" :value="$job->qualifications" rows="3" required />
            <x-form.textarea name="required_skills" label="Required skills" :value="$job->required_skills" rows="2" required />
        </div>
    </x-admin.panel>

    <x-admin.panel title="Application details">
        <div class="grid gap-5 md:grid-cols-3">
            <x-form.field label="Application method" name="application_method" required>
                <select name="application_method" id="application_method" class="field-input" x-model="method">
                    @foreach (App\Enums\ApplicationMethod::options() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </x-form.field>
            <x-form.input name="application_email_url" label="Application email / URL" :value="$job->application_email_url" required />
            <x-form.input name="application_deadline" type="date" label="Application deadline" :value="$job->application_deadline?->toDateString()" required />
        </div>
    </x-admin.panel>

    <x-admin.panel title="Publication & source">
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <x-form.input name="published_date" type="date" label="Published date" :value="$job->published_date?->toDateString()" required />
            <x-form.input name="expiry_date" type="date" label="Expiry date" :value="$job->expiry_date?->toDateString()" required hint="Hidden from the public site after this date." />
            <x-form.select name="status" label="Status" :options="App\Enums\RecordStatus::options()" :value="$job->status" required />
            <x-form.input name="source_name" label="Source name" :value="$job->source_name" required />
            <x-form.input name="source_url" type="url" label="Source URL" :value="$job->source_url" required class="md:col-span-2" hint="The public “Apply Now” button links here." />
        </div>
    </x-admin.panel>
</div>
