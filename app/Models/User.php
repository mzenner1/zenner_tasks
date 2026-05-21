<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUlids;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'google_id',
        'invited_by',
        'last_active_at',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_active_at'    => 'datetime',
            'password'          => 'hashed',
            'preferences'       => 'array',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->withTimestamps();
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_assignees');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Get the user's effective role within a specific project.
     * Always uses the global role — per-project roles are not used.
     */
    public function projectRole(string $projectId): string
    {
        return $this->role;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    // ─── Preferences ─────────────────────────────────────────────────────────

    public function getProjectFiltersPreference(string $projectId): array
    {
        return data_get($this->preferences, 'project_filters.' . $projectId) ?? [];
    }

    public function setProjectFiltersPreference(string $projectId, array $filters): void
    {
        $prefs = $this->preferences ?? [];
        data_set($prefs, 'project_filters.' . $projectId, $filters);
        $this->update(['preferences' => $prefs]);
    }

    public function getProjectSortPreference(string $projectId): ?string
    {
        return data_get($this->preferences, 'project_sort.' . $projectId);
    }

    public function setProjectSortPreference(string $projectId, ?string $sort): void
    {
        $prefs = $this->preferences ?? [];
        data_set($prefs, 'project_sort.' . $projectId, $sort);
        $this->update(['preferences' => $prefs]);
    }
}
