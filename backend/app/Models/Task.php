<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property int $creator_id
 * @property int|null $assignee_id
 * @property string $title
 * @property string|null $description
 * @property TaskStatus $status
 * @property TaskPriority $priority
 * @property Carbon|null $due_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $search_vector
 * @property-read User|null $assignee
 * @property-read User|null $creator
 * @property-read Project|null $project
 * @method static Builder<static>|Task newModelQuery()
 * @method static Builder<static>|Task newQuery()
 * @method static Builder<static>|Task query()
 * @method static Builder<static>|Task whereAssigneeId($value)
 * @method static Builder<static>|Task whereCreatedAt($value)
 * @method static Builder<static>|Task whereCreatorId($value)
 * @method static Builder<static>|Task whereDescription($value)
 * @method static Builder<static>|Task whereDueDate($value)
 * @method static Builder<static>|Task whereId($value)
 * @method static Builder<static>|Task wherePriority($value)
 * @method static Builder<static>|Task whereProjectId($value)
 * @method static Builder<static>|Task whereSearchVector($value)
 * @method static Builder<static>|Task whereStatus($value)
 * @method static Builder<static>|Task whereTitle($value)
 * @method static Builder<static>|Task whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Task extends Model
{
    protected $fillable = [
        'project_id',
        'creator_id',
        'assignee_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'status'   => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
}
