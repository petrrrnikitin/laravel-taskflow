<?php

namespace Tests\Unit\Services;

use App\Enums\ProjectRole;
use App\Events\ProjectMemberInvited;
use App\Exceptions\CannotRemoveOwnerException;
use App\Exceptions\InviteeNotFoundException;
use App\Exceptions\MemberAlreadyExistsException;
use App\Models\Project;
use App\Models\User;
use App\Repositories\Contracts\ProjectMemberRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\ProjectMemberService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\TestCase;

class ProjectMemberServiceTest extends TestCase
{
    private ProjectMemberRepositoryInterface&MockInterface $memberRepo;

    private UserRepositoryInterface&MockInterface $userRepo;

    private ProjectMemberService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->memberRepo = $this->mock(ProjectMemberRepositoryInterface::class);
        $this->userRepo = $this->mock(UserRepositoryInterface::class);
        $this->service = app(ProjectMemberService::class);
    }

    public function test_get_members_delegates_to_repository(): void
    {
        $project = new Project;
        $collection = new Collection([new User]);

        $this->memberRepo->shouldReceive('allForProject')->with($project)->once()->andReturn($collection);

        $result = $this->service->getMembers($project);

        $this->assertSame($collection, $result);
    }

    public function test_invite_adds_member_and_dispatches_event(): void
    {
        Event::fake();

        $project = new Project;
        $invitee = new User;
        $invitee->id = 5;
        $actor = new User;

        $this->userRepo->shouldReceive('findById')->with(5)->once()->andReturn($invitee);
        $this->memberRepo->shouldReceive('isMember')->with($project, $invitee)->once()->andReturn(false);
        $this->memberRepo->shouldReceive('add')->with($project, $invitee, ProjectRole::Member)->once();

        $this->service->invite($project, 5, $actor);

        Event::assertDispatched(ProjectMemberInvited::class, fn ($e) => $e->invitee === $invitee && $e->actor === $actor);
    }

    public function test_invite_throws_when_user_does_not_exist(): void
    {
        $this->userRepo->shouldReceive('findById')->with(99)->once()->andReturn(null);

        $this->expectException(InviteeNotFoundException::class);

        $this->service->invite(new Project, 99, new User);
    }

    public function test_invite_throws_when_user_is_already_a_member(): void
    {
        $invitee = new User;
        $invitee->id = 3;
        $project = new Project;

        $this->userRepo->shouldReceive('findById')->with(3)->once()->andReturn($invitee);
        $this->memberRepo->shouldReceive('isMember')->with($project, $invitee)->once()->andReturn(true);

        $this->expectException(MemberAlreadyExistsException::class);

        $this->service->invite($project, 3, new User);
    }

    public function test_remove_delegates_to_repository_when_member_is_not_owner(): void
    {
        $project = new Project;
        $project->owner_id = 1;

        $member = new User;
        $member->id = 2;

        $this->memberRepo->shouldReceive('remove')->with($project, $member)->once();

        $this->service->remove($project, $member);
    }

    public function test_remove_throws_when_trying_to_remove_the_owner(): void
    {
        $project = new Project;
        $project->owner_id = 1;

        $owner = new User;
        $owner->id = 1;

        $this->expectException(CannotRemoveOwnerException::class);

        $this->service->remove($project, $owner);
    }
}
