<?php

namespace App\Models;

use App\Enums\ActivityAction;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $task_id
 * @property int $actor_id
 * @property ActivityAction $action
 * @property array<array-key, mixed> $properties
 * @property Carbon $created_at
 * @property-read User|null $actor
 * @property-read Task|null $task
 *
 * @method static Builder<static>|ActivityLog newModelQuery()
 * @method static Builder<static>|ActivityLog newQuery()
 * @method static Builder<static>|ActivityLog query()
 * @method static Builder<static>|ActivityLog whereAction($value)
 * @method static Builder<static>|ActivityLog whereActorId($value)
 * @method static Builder<static>|ActivityLog whereCreatedAt($value)
 * @method static Builder<static>|ActivityLog whereId($value)
 * @method static Builder<static>|ActivityLog whereProperties($value)
 * @method static Builder<static>|ActivityLog whereTaskId($value)
 *
 * @mixin Eloquent
 */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'task_id',
        'actor_id',
        'action',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'action' => ActivityAction::class,
            'properties' => 'array',
        ];
    }

    /** @return BelongsTo<Task, $this> */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
