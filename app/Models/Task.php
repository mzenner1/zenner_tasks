<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'project_id',
        'task_number',
        'status_id',
        'created_by',
        'title',
        'description',
        'priority',
        'due_date',
        'sort_order',
        'is_archived',
        'last_activity_at',
    ];

    protected $casts = [
        'due_date'    => 'date',
        'is_archived' => 'boolean',
        'sort_order'  => 'integer',
        'task_number'      => 'integer',
        'last_activity_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if (empty($task->task_number)) {
                $max = static::where('project_id', $task->project_id)->max('task_number');
                $task->task_number = ($max ?? 0) + 1;
            }
        });
    }

    /**
     * Returns "#42" style label for display.
     */
    public function getTaskNumberLabelAttribute(): string
    {
        return '#' . $this->task_number;
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignees');
    }

    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_watchers')->withTimestamps();
    }

    /**
     * Add a user as a watcher (idempotent).
     */
    public function addWatcher(string $userId): void
    {
        if (!$this->watchers()->where('user_id', $userId)->exists()) {
            $this->watchers()->attach($userId);
        }
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function activityLog(): HasMany
    {
        return $this->hasMany(ActivityLog::class)->latest('created_at');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /**
     * Tasks that are past their due date and not in a closed status.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereHas('status', fn (Builder $q) => $q->where('is_closed', false));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_archived',
        'last_activity_at', false);
    }

    public function scopeForProject(Builder $query, string $projectId): Builder
    {
        return $query->where('project_id', $projectId);
    }
}
