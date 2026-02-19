<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $name
 * @property string|null $description
 * @property ProjectStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, User> $members
 * @property-read int|null $members_count
 * @property-read User|null $owner
 * @method static Builder<static>|Project newModelQuery()
 * @method static Builder<static>|Project newQuery()
 * @method static Builder<static>|Project query()
 * @method static Builder<static>|Project whereCreatedAt($value)
 * @method static Builder<static>|Project whereDescription($value)
 * @method static Builder<static>|Project whereId($value)
 * @method static Builder<static>|Project whereName($value)
 * @method static Builder<static>|Project whereOwnerId($value)
 * @method static Builder<static>|Project whereStatus($value)
 * @method static Builder<static>|Project whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Project extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }
}
