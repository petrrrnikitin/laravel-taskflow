<?php

namespace Tests\Unit\Services;

use App\DTO\Auth\AuthResultDTO;
use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Models\User;
use App\Repositories\Contracts\TokenRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private UserRepositoryInterface&MockInterface $userRepo;

    /** @var TokenRepositoryInterface&MockInterface */
    private TokenRepositoryInterface $tokenRepo;

    private AuthService $service;

    private int $expectedExpiresIn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = $this->mock(UserRepositoryInterface::class);
        $this->tokenRepo = $this->mock(TokenRepositoryInterface::class);
        $this->service = app(AuthService::class);
        $this->expectedExpiresIn = (int) config('sanctum.access_token_ttl') * 60;
    }

    public function test_register_creates_user_and_returns_dto(): void
    {
        $dto = new RegisterDTO('John', 'john@example.com', 'secret');

        $accessToken = Mockery::mock(NewAccessToken::class);
        $accessToken->plainTextToken = 'access-token';
        $refreshToken = Mockery::mock(NewAccessToken::class);
        $refreshToken->plainTextToken = 'refresh-token';

        $user = Mockery::mock(User::class);

        $this->userRepo->shouldReceive('create')->with($dto)->once()->andReturn($user);

        $this->tokenRepo->shouldReceive('createToken')
            ->with($user, 'access', ['*'], Mockery::type(Carbon::class))
            ->once()
            ->andReturn($accessToken);
        $this->tokenRepo->shouldReceive('createToken')
            ->with($user, 'refresh', ['refresh'], Mockery::type(Carbon::class))
            ->once()
            ->andReturn($refreshToken);

        $result = $this->service->register($dto);

        $this->assertInstanceOf(AuthResultDTO::class, $result);
        $this->assertSame($user, $result->user);
        $this->assertSame('access-token', $result->accessToken);
        $this->assertSame('refresh-token', $result->refreshToken);
        $this->assertSame($this->expectedExpiresIn, $result->expiresIn);
    }

    public function test_login_returns_dto_when_credentials_are_valid(): void
    {
        $plainPassword = 'secret';
        $hashedPassword = Hash::make($plainPassword);

        $accessToken = Mockery::mock(NewAccessToken::class);
        $accessToken->plainTextToken = 'access-token';
        $refreshToken = Mockery::mock(NewAccessToken::class);
        $refreshToken->plainTextToken = 'refresh-token';

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('password')->andReturn($hashedPassword);

        $dto = new LoginDTO('john@example.com', $plainPassword);
        $this->userRepo->shouldReceive('findByEmail')->with($dto->email)->once()->andReturn($user);

        $this->tokenRepo->shouldReceive('deleteForUser')->with($user)->once();
        $this->tokenRepo->shouldReceive('createToken')
            ->with($user, 'access', ['*'], Mockery::type(Carbon::class))
            ->once()
            ->andReturn($accessToken);
        $this->tokenRepo->shouldReceive('createToken')
            ->with($user, 'refresh', ['refresh'], Mockery::type(Carbon::class))
            ->once()
            ->andReturn($refreshToken);

        $result = $this->service->login($dto);

        $this->assertInstanceOf(AuthResultDTO::class, $result);
        $this->assertSame($user, $result->user);
        $this->assertSame('access-token', $result->accessToken);
        $this->assertSame('refresh-token', $result->refreshToken);
        $this->assertSame($this->expectedExpiresIn, $result->expiresIn);
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

    public function test_logout_deletes_all_tokens(): void
    {
        $user = Mockery::mock(User::class);

        $this->tokenRepo->shouldReceive('deleteForUser')->with($user)->once();

        $this->service->logout($user);
    }

    public function test_refresh_returns_new_tokens_when_token_is_valid(): void
    {
        $user = Mockery::mock(User::class);

        $accessToken = Mockery::mock(NewAccessToken::class);
        $accessToken->plainTextToken = 'new-access-token';
        $refreshToken = Mockery::mock(NewAccessToken::class);
        $refreshToken->plainTextToken = 'new-refresh-token';

        $token = Mockery::mock(PersonalAccessToken::class);
        $token->shouldReceive('getAttribute')->with('tokenable')->andReturn($user);

        $this->tokenRepo->shouldReceive('consumeRefreshToken')
            ->with('valid-raw-token')
            ->once()
            ->andReturn($token);
        $this->tokenRepo->shouldReceive('createToken')
            ->with($user, 'access', ['*'], Mockery::type(Carbon::class))
            ->once()
            ->andReturn($accessToken);
        $this->tokenRepo->shouldReceive('createToken')
            ->with($user, 'refresh', ['refresh'], Mockery::type(Carbon::class))
            ->once()
            ->andReturn($refreshToken);

        $result = $this->service->refresh('valid-raw-token');

        $this->assertInstanceOf(AuthResultDTO::class, $result);
        $this->assertSame($user, $result->user);
        $this->assertSame('new-access-token', $result->accessToken);
        $this->assertSame('new-refresh-token', $result->refreshToken);
        $this->assertSame($this->expectedExpiresIn, $result->expiresIn);
    }

    public function test_refresh_throws_when_token_not_found(): void
    {
        $this->tokenRepo->shouldReceive('consumeRefreshToken')
            ->with('bad-token')
            ->once()
            ->andReturn(null);

        $this->expectException(AuthenticationException::class);
        $this->service->refresh('bad-token');
    }

    public function test_refresh_throws_when_tokenable_is_not_user(): void
    {
        $token = Mockery::mock(PersonalAccessToken::class);
        $token->shouldReceive('getAttribute')->with('tokenable')->andReturn(new \stdClass());

        $this->tokenRepo->shouldReceive('consumeRefreshToken')
            ->with('orphaned-token')
            ->once()
            ->andReturn($token);

        $this->expectException(AuthenticationException::class);
        $this->service->refresh('orphaned-token');
    }
}
