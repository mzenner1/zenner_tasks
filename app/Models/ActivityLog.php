<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    public $timestamps = false; // Only has created_at

    protected $table = 'activity_log';

    protected $fillable = [
        'task_id',
        'user_id',
        'event',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Return a human-readable description of the event for display in the activity log.
     */
    public function description(): string
    {
        return match ($this->event) {
            'status_changed'   => 'changed the status from "' . ($this->properties['from'] ?? '?') . '" to "' . ($this->properties['to'] ?? '?') . '"',
            'due_date_changed' => 'changed the due date to ' . ($this->properties['to'] ?? 'none'),
            'assigned'         => 'assigned this task to ' . ($this->properties['to'] ?? 'someone'),
            'unassigned'       => 'removed ' . ($this->properties['from'] ?? 'someone') . ' from this task',
            'commented'        => 'added a comment',
            'priority_changed' => 'changed priority from "' . ($this->properties['from'] ?? '?') . '" to "' . ($this->properties['to'] ?? '?') . '"',
            'created'          => 'created this task',
            'archived'         => 'archived this task',
            'restored'         => 'restored this task',
            'copied_from'      => 'copied from ' . ($this->properties['from'] ?? 'another task'),
            'moved_project'    => 'moved this task from "' . ($this->properties['from'] ?? '?') . '" to "' . ($this->properties['to'] ?? '?') . '"',
            default            => $this->event,
        };
    }
}
