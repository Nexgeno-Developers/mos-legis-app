<?php

namespace App\Http\Requests;

use App\Enums\EmploymentType;
use App\Enums\RecordStatus;
use App\Enums\WorkMode;
use App\Models\JobPosting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * SOW A.04 job fields; shared by the admin panel and the author portal (B.06).
 * Published date, expiry date and status are not asked for — see jobData().
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
            'responsibilities' => ['nullable', 'string', 'max:5000'],
            'qualifications' => ['nullable', 'string', 'max:5000'],
            'required_skills' => ['nullable', 'string', 'max:2000'],
            // A new job needs a deadline that has not passed; an existing one may keep its old date.
            'application_deadline' => ['required', 'date', ...($this->editing() ? [] : ['after_or_equal:today'])],
            'source_name' => ['nullable', 'string', 'max:150'],
            'source_url' => ['required', 'url:https,http', 'max:255'],
            'status' => ['nullable', Rule::enum(RecordStatus::class)],
        ];
    }

    public function messages(): array
    {
        return ['application_deadline.after_or_equal' => 'The application deadline cannot be in the past.'];
    }

    /**
     * Validated data plus what is filled in automatically: the job goes live on the day it is
     * posted, stays listed until its application deadline, and is Active unless set otherwise.
     */
    public function jobData(): array
    {
        $data = $this->validated();
        $data['expiry_date'] = $data['application_deadline'];
        $data['status'] ??= RecordStatus::Active->value;

        if (! $this->editing()) {
            $data['published_date'] = today();
        }

        return $data;
    }

    private function editing(): bool
    {
        return $this->route('job_posting') !== null || $this->route('job') !== null;
    }
}
