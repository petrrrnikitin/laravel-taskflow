<?php

namespace Tests\Unit\Services;

use App\DTO\Auth\AuthResultDTO;
use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private UserRepositoryInterface&MockInterface $userRepo;

    private AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = $this->mock(UserRepositoryInterface::class);
        $this->service = app(AuthService::class);
    }

    public function test_register_creates_user_and_returns_dto(): void
    {
        $dto = new RegisterDTO('John', 'john@example.com', 'secret');
        $token = new class()
        {
            public string $plainTextToken = 'raw-token';
        };

        $user = Mockery::mock(User::class);
        $user->shouldReceive('createToken')->with('api')->once()->andReturn($token);

        $this->userRepo->shouldReceive('create')->with($dto)->once()->andReturn($user);

        $result = $this->service->register($dto);

        $this->assertInstanceOf(AuthResultDTO::class, $result);
        $this->assertSame($user, $result->user);
        $this->assertSame('raw-token', $result->token);
    }

    public function test_login_returns_dto_when_credentials_are_valid(): void
    {
        $plainPassword = 'secret';
        $hashedPassword = Hash::make($plainPassword);
        $token = new class()
        {
            public string $plainTextToken = 'raw-token';
        };
        $tokensRelation = Mockery::mock(['delete' => null]);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('password')->andReturn($hashedPassword);
        $user->shouldReceive('tokens')->andReturn($tokensRelation);
        $user->shouldReceive('createToken')->with('api')->once()->andReturn($token);

        $dto = new LoginDTO('john@example.com', $plainPassword);

        $this->userRepo->shouldReceive('findByEmail')->with($dto->email)->once()->andReturn($user);
        Auth::shouldReceive('login')->once()->with($user);

        $result = $this->service->login($dto);

        $this->assertInstanceOf(AuthResultDTO::class, $result);
        $this->assertSame($user, $result->user);
        $this->assertSame('raw-token', $result->token);
    }

    public function test_login_throws_when_user_is_not_found(): void
    {
        $dto = new LoginDTO('nobody@example.com', 'secret');
        $this->userRepo->shouldReceive('findByEmail')->with($dto->email)->once()->andReturn(null);

        $this->expectException(AuthenticationException::class);
        $this->service->login($dto);
    }

    public function test_login_throws_when_password_is_wrong(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('password')->andReturn(Hash::make('correct-password'));

        $dto = new LoginDTO('john@example.com', 'wrong-password');
        $this->userRepo->shouldReceive('findByEmail')->with($dto->email)->once()->andReturn($user);

        $this->expectException(AuthenticationException::class);
        $this->service->login($dto);
    }

    public function test_logout_deletes_current_access_token(): void
    {
        $token = Mockery::mock();
        $token->shouldReceive('delete')->once();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('currentAccessToken')->once()->andReturn($token);

        $this->service->logout($user);
    }
}
