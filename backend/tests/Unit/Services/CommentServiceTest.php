<?php

namespace Tests\Unit\Services;

use App\DTO\Comment\CreateCommentDTO;
use App\DTO\Comment\UpdateCommentDTO;
use App\Models\Comment;
use App\Models\Task;
use App\Repositories\Contracts\CommentRepositoryInterface;
use App\Services\CommentService;
use Illuminate\Database\Eloquent\Collection;
use Mockery\MockInterface;
use Tests\TestCase;

class CommentServiceTest extends TestCase
{
    private CommentRepositoryInterface&MockInterface $commentRepo;

    private CommentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentRepo = $this->mock(CommentRepositoryInterface::class);
        $this->service = app(CommentService::class);
    }

    public function test_get_for_task_delegates_to_repository(): void
    {
        $task = new Task;
        $collection = new Collection([new Comment]);

        $this->commentRepo->shouldReceive('allForTask')->with($task)->once()->andReturn($collection);

        $result = $this->service->getForTask($task);

        $this->assertSame($collection, $result);
    }

    public function test_create_delegates_to_repository(): void
    {
        $dto = new CreateCommentDTO(taskId: 1, authorId: 1, body: 'Hello');
        $comment = new Comment;

        $this->commentRepo->shouldReceive('create')->with($dto)->once()->andReturn($comment);

        $result = $this->service->create($dto);

        $this->assertSame($comment, $result);
    }

    public function test_update_delegates_to_repository(): void
    {
        $comment = new Comment;
        $dto = new UpdateCommentDTO(body: 'Updated body');
        $updated = new Comment;

        $this->commentRepo->shouldReceive('update')->with($comment, $dto)->once()->andReturn($updated);

        $result = $this->service->update($comment, $dto);

        $this->assertSame($updated, $result);
    }

    public function test_delete_delegates_to_repository(): void
    {
        $comment = new Comment;

        $this->commentRepo->shouldReceive('delete')->with($comment)->once();

        $this->service->delete($comment);
    }
}
