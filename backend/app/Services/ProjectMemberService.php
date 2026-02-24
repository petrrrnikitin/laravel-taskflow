<?php

namespace App\Services;

use App\Enums\ProjectRole;
use App\Events\ProjectMemberInvited;
use App\Exceptions\CannotRemoveOwnerException;
use App\Exceptions\InviteeNotFoundException;
use App\Exceptions\MemberAlreadyExistsException;
use App\Models\Project;
use App\Models\User;
use App\Repositories\Contracts\ProjectMemberRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

readonly class ProjectMemberService
{
    public function __construct(
        private ProjectMemberRepositoryInterface $members,
        private UserRepositoryInterface $users,
    ) {
    }

    /** @return Collection<int, User> */
    public function getMembers(Project $project): Collection
    {
        return $this->members->allForProject($project);
    }

    public function invite(Project $project, int $userId, User $actor): void
    {
        $invitee = $this->users->findById($userId);

        if (! $invitee) {
            throw new InviteeNotFoundException('User not found.');
        }

        if ($this->members->isMember($project, $invitee)) {
            throw new MemberAlreadyExistsException('User is already a member of this project.');
        }

        $this->members->add($project, $invitee, ProjectRole::Member);

        ProjectMemberInvited::dispatch($project, $invitee, $actor);
    }

    public function remove(Project $project, User $member): void
    {
        if ($project->isOwnedBy($member)) {
            throw new CannotRemoveOwnerException('Cannot remove the project owner.');
        }

        $this->members->remove($project, $member);
    }
}
