<?php

namespace App\Models;

use App\Enums\ManuscriptStage;
use App\Enums\RecordStatus;
use App\Enums\RoleName;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'status', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => RecordStatus::class,
        ];
    }

    public function authorProfile(): HasOne
    {
        return $this->hasOne(AuthorProfile::class);
    }

    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(UserSocialAccount::class);
    }

    /** Content categories a reviewer covers (SOW A.09). */
    public function reviewerContentCategories(): BelongsToMany
    {
        return $this->belongsToMany(ContentCategory::class, 'reviewer_content_categories', 'user_id', 'content_category_id')
            ->withPivot('created_at');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ManuscriptSubmission::class);
    }

    public function assignedSubmissions(): HasMany
    {
        return $this->hasMany(ManuscriptSubmission::class, 'assigned_to');
    }

    public function activeAssignments(): HasMany
    {
        return $this->assignedSubmissions()->whereIn('stage', ManuscriptStage::activeReview());
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }

    public function isActive(): bool
    {
        return $this->status === RecordStatus::Active;
    }

    public function isSuperadmin(): bool
    {
        return $this->hasRole(RoleName::Superadmin->value);
    }

    public function isAuthor(): bool
    {
        return $this->hasRole(RoleName::Author->value);
    }

    public function canAccessAdmin(): bool
    {
        return $this->hasAnyRole([RoleName::Superadmin->value, RoleName::Reviewer->value]);
    }

    public function primaryRole(): ?RoleName
    {
        $name = $this->relationLoaded('roles') ? $this->roles->first()?->name : $this->getRoleNames()->first();

        return $name ? RoleName::tryFrom($name) : null;
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', RecordStatus::Active);
    }
}
