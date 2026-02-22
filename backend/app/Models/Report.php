<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property int $requested_by
 * @property ReportStatus $status
 * @property string|null $file_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Project|null $project
 * @property-read User|null $requester
 * @method static Builder<static>|Report newModelQuery()
 * @method static Builder<static>|Report newQuery()
 * @method static Builder<static>|Report query()
 * @method static Builder<static>|Report whereCreatedAt($value)
 * @method static Builder<static>|Report whereFilePath($value)
 * @method static Builder<static>|Report whereId($value)
 * @method static Builder<static>|Report whereProjectId($value)
 * @method static Builder<static>|Report whereRequestedBy($value)
 * @method static Builder<static>|Report whereStatus($value)
 * @method static Builder<static>|Report whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Report extends Model
{
    protected $fillable = [
        'project_id',
        'requested_by',
        'status',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReportStatus::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}