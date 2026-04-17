<?php

namespace Tests\Unit\Services;

use App\DTO\Task\CreateTaskDTO;
use App\DTO\Task\SearchTaskDTO;
use App\DTO\Task\UpdateTaskDTO;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Events\TaskAssigned;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Events\TaskUpdated;
use App\Exceptions\AssigneeNotMemberException;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Contracts\ProjectMemberRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\TaskService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    private TaskRepositoryInterface&MockInterface $taskRepo;

    private UserRepositoryInterface&MockInterface $userRepo;

    private ProjectMemberRepositoryInterface&MockInterface $memberRepo;

    private TaskService $service;

    private User $actor;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        // Map redis cache store to array driver so Cache::store('redis')->tags() works without Redis
        config(['cache.stores.redis' => ['driver' => 'array', 'serialize' => false]]);

        $this->taskRepo = $this->mock(TaskRepositoryInterface::class);
        $this->userRepo = $this->mock(UserRepositoryInterface::class);
        $this->memberRepo = $this->mock(ProjectMemberRepositoryInterface::class);
        $this->service = app(TaskService::class);

        $this->actor = new User();
        $this->actor->id = 1;
        $this->actingAs($this->actor);

        $this->project = new Project();
        $this->project->id = 1;
    }

    public function test_get_for_project_returns_collection_from_repository(): void
    {
        $project = new Project();
        $project->id = 1;

        $collection = new Collection([new Task()]);

        $this->taskRepo->shouldReceive('allForProject')->once()->with($project)->andReturn($collection);

        $result = $this->service->getForProject($project);

        $this->assertSame($collection, $result);
    }

    public function test_get_for_project_returns_cached_result_on_second_call(): void
    {
        $project = new Project();
        $project->id = 2;

        $collection = new Collection([new Task()]);

        $this->taskRepo->shouldReceive('allForProject')->once()->andReturn($collection);

        $this->service->getForProject($project);
        $result = $this->service->getForProject($project);

        $this->assertSame($collection, $result);
    }

    public function test_search_delegates_to_repository(): void
    {
        $user = new User();
        $dto = new SearchTaskDTO(q: 'foo', status: null, priority: null, assigneeId: null, perPage: 15);
        $paginator = new LengthAwarePaginator([], 0, 15);

        $this->taskRepo->shouldReceive('search')->with($user, $dto)->once()->andReturn($paginator);

        $result = $this->service->search($user, $dto);

        $this->assertSame($paginator, $result);
    }

    public function test_create_dispatches_task_created_event(): void
    {
        Event::fake();

        $dto = new CreateTaskDTO(
            projectId: 1,
            creatorId: 1,
            title: 'New Task',
            description: null,
            status: TaskStatus::Todo,
            priority: TaskPriority::Medium,
            dueDate: null,
            assigneeId: null,
        );
        $task = new Task(['project_id' => 1, 'title' => 'New Task', 'creator_id' => 1, 'assignee_id' => null]);

        $this->taskRepo->shouldReceive('create')->with($dto)->once()->andReturn($task);

        $this->service->create($dto, $this->project);

        Event::assertDispatched(TaskCreated::class, fn ($e) => $e->task === $task && $e->actor === $this->actor);
    }

    public function test_create_dispatches_task_assigned_event_when_assignee_is_set(): void
    {
        Event::fake();

        $assignee = new User();
        $assignee->id = 2;

        $dto = new CreateTaskDTO(
            projectId: 1,
            creatorId: 1,
            title: 'Assigned Task',
            description: null,
            status: TaskStatus::Todo,
            priority: TaskPriority::Medium,
            dueDate: null,
            assigneeId: 2,
        );

        $task = new Task(['project_id' => 1, 'title' => 'Assigned Task', 'creator_id' => 1, 'assignee_id' => 2]);
        $task->setRelation('assignee', $assignee);

        $this->userRepo->shouldReceive('findById')->with(2)->once()->andReturn($assignee);
        $this->memberRepo->shouldReceive('isMember')->with($this->project, $assignee)->once()->andReturn(true);
        $this->taskRepo->shouldReceive('create')->with($dto)->once()->andReturn($task);

        $this->service->create($dto, $this->project);

        Event::assertDispatched(TaskCreated::class);
        Event::assertDispatched(TaskAssigned::class, fn ($e) => $e->task === $task && $e->assignee === $assignee);
    }

    public function test_create_does_not_dispatch_task_assigned_event_when_no_assignee(): void
    {
        Event::fake();

        $dto = new CreateTaskDTO(
            projectId: 1,
            creatorId: 1,
            title: 'Unassigned Task',
            description: null,
            status: TaskStatus::Todo,
            priority: TaskPriority::Medium,
            dueDate: null,
            assigneeId: null,
        );
        $task = new Task(['project_id' => 1, 'title' => 'Unassigned Task', 'creator_id' => 1, 'assignee_id' => null]);

        $this->taskRepo->shouldReceive('create')->with($dto)->once()->andReturn($task);

        $this->service->create($dto, $this->project);

        Event::assertDispatched(TaskCreated::class);
        Event::assertNotDispatched(TaskAssigned::class);
    }

    public function test_create_throws_when_assignee_is_not_project_member(): void
    {
        $nonMember = new User();
        $nonMember->id = 99;

        $dto = new CreateTaskDTO(
            projectId: 1,
            creatorId: 1,
            title: 'Task',
            description: null,
            status: TaskStatus::Todo,
            priority: TaskPriority::Medium,
            dueDate: null,
            assigneeId: 99,
        );

        $this->userRepo->shouldReceive('findById')->with(99)->once()->andReturn($nonMember);
        $this->memberRepo->shouldReceive('isMember')->with($this->project, $nonMember)->once()->andReturn(false);

        $this->expectException(AssigneeNotMemberException::class);

        $this->service->create($dto, $this->project);
    }

    public function test_create_throws_when_assignee_does_not_exist(): void
    {
        $dto = new CreateTaskDTO(
            projectId: 1,
            creatorId: 1,
            title: 'Task',
            description: null,
            status: TaskStatus::Todo,
            priority: TaskPriority::Medium,
            dueDate: null,
            assigneeId: 999,
        );

        $this->userRepo->shouldReceive('findById')->with(999)->once()->andReturn(null);

        $this->expectException(AssigneeNotMemberException::class);

        $this->service->create($dto, $this->project);
    }

    public function test_update_dispatches_task_updated_event_when_fields_changed(): void
    {
        Event::fake();

        $task = new Task(['project_id' => 1, 'title' => 'Old Title', 'description' => null, 'priority' => 'medium', 'due_date' => null, 'assignee_id' => null]);

        $dto = new UpdateTaskDTO(
            title: 'New Title',
            description: null,
            priority: TaskPriority::Medium,
            dueDate: null,
            assigneeId: null,
        );

        $updated = new Task(['project_id' => 1, 'title' => 'New Title', 'description' => null, 'priority' => 'medium', 'assignee_id' => null]);

        $this->taskRepo->shouldReceive('update')->with($task, $dto)->once()->andReturn($updated);

        $this->service->update($task, $dto, $this->project);

        Event::assertDispatched(TaskUpdated::class, function ($e) use ($updated) {
            return $e->task === $updated && isset($e->changes['title']);
        });
    }

    public function test_update_does_not_dispatch_task_updated_event_when_nothing_changed(): void
    {
        Event::fake();

        $task = new Task(['project_id' => 1, 'title' => 'Same Title', 'description' => null, 'priority' => 'medium', 'due_date' => null, 'assignee_id' => null]);

        $dto = new UpdateTaskDTO(
            title: 'Same Title',
            description: null,
            priority: TaskPriority::Medium,
            dueDate: null,
            assigneeId: null,
        );

        $updated = new Task(['project_id' => 1, 'title' => 'Same Title', 'description' => null, 'priority' => 'medium', 'assignee_id' => null]);

        $this->taskRepo->shouldReceive('update')->with($task, $dto)->once()->andReturn($updated);

        $this->service->update($task, $dto, $this->project);

        Event::assertNotDispatched(TaskUpdated::class);
    }

    public function test_update_dispatches_task_assigned_event_when_assignee_changes(): void
    {
        Event::fake();

        $task = new Task(['project_id' => 1, 'title' => 'T', 'description' => null, 'priority' => 'medium', 'due_date' => null, 'assignee_id' => null]);

        $dto = new UpdateTaskDTO(
            title: 'T',
            description: null,
            priority: TaskPriority::Medium,
            dueDate: null,
            assigneeId: 2,
        );

        $assignee = new User();
        $assignee->id = 2;

        $updated = Mockery::mock(Task::class)->makePartial();
        $updated->shouldReceive('load')->with('assignee')->andReturnSelf();
        $updated->setRelation('assignee', $assignee);

        $this->userRepo->shouldReceive('findById')->with(2)->once()->andReturn($assignee);
        $this->memberRepo->shouldReceive('isMember')->with($this->project, $assignee)->once()->andReturn(true);
        $this->taskRepo->shouldReceive('update')->with($task, $dto)->once()->andReturn($updated);

        $this->service->update($task, $dto, $this->project);

        Event::assertDispatched(TaskAssigned::class, fn ($e) => $e->assignee === $assignee);
    }

    public function test_update_throws_when_new_assignee_is_not_project_member(): void
    {
        $task = new Task(['project_id' => 1, 'title' => 'T', 'description' => null, 'priority' => 'medium', 'due_date' => null, 'assignee_id' => null]);

        $nonMember = new User();
        $nonMember->id = 99;

        $dto = new UpdateTaskDTO(
            title: 'T',
            description: null,
            priority: TaskPriority::Medium,
            dueDate: null,
            assigneeId: 99,
        );

        $this->userRepo->shouldReceive('findById')->with(99)->once()->andReturn($nonMember);
        $this->memberRepo->shouldReceive('isMember')->with($this->project, $nonMember)->once()->andReturn(false);

        $this->expectException(AssigneeNotMemberException::class);

        $this->service->update($task, $dto, $this->project);
    }

    public function test_update_does_not_check_membership_when_assignee_is_unchanged(): void
    {
        Event::fake();

        $task = new Task(['project_id' => 1, 'title' => 'T', 'description' => null, 'priority' => 'medium', 'due_date' => null, 'assignee_id' => 5]);

        $dto = new UpdateTaskDTO(
            title: 'T',
            description: null,
            priority: TaskPriority::Medium,
            dueDate: null,
            assigneeId: 5,
        );

        $updated = new Task(['project_id' => 1, 'title' => 'T', 'description' => null, 'priority' => 'medium', 'assignee_id' => 5]);

        $this->taskRepo->shouldReceive('update')->with($task, $dto)->once()->andReturn($updated);

        // Guard must not be called when assignee doesn't change
        $this->userRepo->shouldNotReceive('findById');
        $this->memberRepo->shouldNotReceive('isMember');

        $this->service->update($task, $dto, $this->project);
    }

    public function test_change_status_dispatches_task_status_changed_event(): void
    {
        Event::fake();

        $task = new Task(['status' => 'todo', 'project_id' => 1]);
        $oldStatus = TaskStatus::Todo;
        $newStatus = TaskStatus::Done;
        $updatedTask = new Task(['status' => 'done', 'project_id' => 1]);

        $this->taskRepo->shouldReceive('changeStatus')->with($task, $newStatus)->once()->andReturn($updatedTask);

        $this->service->changeStatus($task, $newStatus);

        Event::assertDispatched(TaskStatusChanged::class, function ($e) use ($updatedTask, $oldStatus, $newStatus) {
            return $e->task === $updatedTask
                && $e->oldStatus === $oldStatus
                && $e->newStatus === $newStatus;
        });
    }

    public function test_delete_delegates_to_repository(): void
    {
        $task = new Task(['project_id' => 1]);

        $this->taskRepo->shouldReceive('delete')->with($task)->once();

        $this->service->delete($task);
    }
}