<?php

namespace Tests\Unit\Services;

use App\DTO\Project\CreateProjectDTO;
use App\DTO\Project\UpdateProjectDTO;
use App\Models\Project;
use App\Models\User;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Services\ProjectService;
use Illuminate\Database\Eloquent\Collection;
use Mockery\MockInterface;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    private ProjectRepositoryInterface&MockInterface $projectRepo;

    private ProjectService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Map the redis cache store to the array driver so Cache::store('redis')->tags() works without Redis
        config(['cache.stores.redis' => ['driver' => 'array', 'serialize' => false]]);

        $this->projectRepo = $this->mock(ProjectRepositoryInterface::class);
        $this->service = app(ProjectService::class);
    }

    public function test_get_for_user_returns_collection_from_repository(): void
    {
        $user = new User;
        $user->id = 1;

        $collection = new Collection([new Project]);

        $this->projectRepo->shouldReceive('allForUser')->once()->with($user)->andReturn($collection);

        $result = $this->service->getForUser($user);

        $this->assertSame($collection, $result);
    }

    public function test_get_for_user_returns_cached_result_on_second_call(): void
    {
        $user = new User;
        $user->id = 2;

        $collection = new Collection([new Project]);

        // Repository must be called exactly once; second call should come from cache
        $this->projectRepo->shouldReceive('allForUser')->once()->andReturn($collection);

        $this->service->getForUser($user);
        $result = $this->service->getForUser($user);

        $this->assertSame($collection, $result);
    }

    public function test_create_delegates_to_repository(): void
    {
        $dto = new CreateProjectDTO(ownerId: 1, name: 'My Project', description: null);
        $project = new Project;

        $this->projectRepo->shouldReceive('create')->with($dto)->once()->andReturn($project);

        $result = $this->service->create($dto);

        $this->assertSame($project, $result);
    }

    public function test_update_delegates_to_repository(): void
    {
        $project = new Project;
        $dto = new UpdateProjectDTO(name: 'New Name', description: null);

        $this->projectRepo->shouldReceive('update')->with($project, $dto)->once()->andReturn($project);

        $result = $this->service->update($project, $dto);

        $this->assertSame($project, $result);
    }

    public function test_archive_delegates_to_repository(): void
    {
        $project = new Project;
        $archived = new Project;

        $this->projectRepo->shouldReceive('archive')->with($project)->once()->andReturn($archived);

        $result = $this->service->archive($project);

        $this->assertSame($archived, $result);
    }

    public function test_delete_delegates_to_repository(): void
    {
        $project = new Project;

        $this->projectRepo->shouldReceive('delete')->with($project)->once();

        $this->service->delete($project);
    }
}
