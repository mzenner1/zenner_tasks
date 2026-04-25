<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Support\MarkdownConverter;

class Comment extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'task_id',
        'user_id',
        'body',
        'parent_id',
        'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Render the comment body as safe HTML (Markdown → HTML).
     */
    public function bodyHtml(): string
    {
        return MarkdownConverter::toHtml($this->body);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /**
     * Hide internal comments from clients.
     */
    public function scopeVisibleTo(Builder $query, User $user, string $projectId): Builder
    {
        if ($user->projectRole($projectId) === 'client') {
            return $query->where('is_internal', false);
        }

        return $query;
    }

    /**
     * Only top-level comments (no parent).
     */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }
}
