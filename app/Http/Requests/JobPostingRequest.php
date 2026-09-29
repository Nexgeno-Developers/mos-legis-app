<?php

namespace App\Http\Requests;

use App\Enums\ApplicationMethod;
use App\Enums\EmploymentType;
use App\Enums\RecordStatus;
use App\Enums\WorkMode;
use App\Models\JobPosting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * SOW A.04 job fields; shared by the admin panel and the author portal (B.06).
 */
class JobPostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $job = $this->route('job_posting');

        return $job ? $this->user()->can('update', $job) : $this->user()->can('create', JobPosting::class);
    }

    public function rules(): array
    {
        return [
            'job_title' => ['required', 'string', 'max:190'],
            'organisation' => ['required', 'string', 'max:190'],
            'location' => ['required', 'string', 'max:150'],
            'work_mode' => ['required', Rule::enum(WorkMode::class)],
            'employment_type' => ['required', Rule::enum(EmploymentType::class)],
            'experience' => ['required', 'string', 'max:60'],
            'practice_area' => ['required', 'string', 'max:120'],
            'salary' => ['nullable', 'string', 'max:120'],
            'summary' => ['required', 'string', 'max:5000'],
            'responsibilities' => ['required', 'string', 'max:5000'],
            'qualifications' => ['required', 'string', 'max:5000'],
            'required_skills' => ['required', 'string', 'max:2000'],
            'application_method' => ['required', Rule::enum(ApplicationMethod::class)],
            'application_email_url' => ['required', 'string', 'max:255',
                $this->input('application_method') === ApplicationMethod::Email->value ? 'email' : 'url:https,http'],
            'published_date' => ['required', 'date'],
            'application_deadline' => ['required', 'date', 'after_or_equal:published_date'],
            'expiry_date' => ['required', 'date', 'after_or_equal:published_date'],
            'source_name' => ['required', 'string', 'max:150'],
            'source_url' => ['required', 'url:https,http', 'max:255'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
        ];
    }
}
