<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Repositories\Contracts\TokenRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class RefreshTokenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->encryptCookies = false;
        $this->withCredentials = true;
    }

    private function getRefreshTokenFromResponse(TestResponse $response): string
    {
        $cookie = $response->getCookie('refresh_token', decrypt: false);
        $this->assertNotNull($cookie, 'refresh_token cookie must be present in response');

        return $cookie->getValue();
    }

    private function getRefreshCsrfFromResponse(TestResponse $response): string
    {
        $cookie = $response->getCookie('refresh_csrf', decrypt: false);
        $this->assertNotNull($cookie, 'refresh_csrf cookie must be present in response');

        return $cookie->getValue();
    }

    private function makeAuthUser(): User&MockInterface
    {
        /** @var User&MockInterface $user */
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']);
        $user->exists = true;

        return $user;
    }

    private function bindTokenRepo(MockInterface $mock): void
    {
        $this->instance(TokenRepositoryInterface::class, $mock);
    }

    private function bindUserRepo(MockInterface $mock): void
    {
        $this->instance(UserRepositoryInterface::class, $mock);
    }

    public function test_refresh_returns_new_access_token_and_sets_rotated_cookie(): void
    {
        $user = $this->makeAuthUser();

        $token = Mockery::mock(PersonalAccessToken::class);
        $token->shouldReceive('getAttribute')->with('tokenable')->andReturn($user);

        $tokenRepo = Mockery::mock(TokenRepositoryInterface::class);
        $tokenRepo->shouldReceive('consumeRefreshToken')
            ->once()
            ->with('valid-refresh-token')
            ->andReturn($token);
        $accessToken = Mockery::mock(NewAccessToken::class);
        $accessToken->plainTextToken = 'new-access-token';
        $refreshToken = Mockery::mock(NewAccessToken::class);
        $refreshToken->plainTextToken = 'new-refresh-token';
        $tokenRepo->shouldReceive('createToken')
            ->with($user, 'access', ['*'], Mockery::type(Carbon::class))
            ->once()
            ->andReturn($accessToken);
        $tokenRepo->shouldReceive('createToken')
            ->with($user, 'refresh', ['refresh'], Mockery::type(Carbon::class))
            ->once()
            ->andReturn($refreshToken);
        $this->bindTokenRepo($tokenRepo);
        $this->bindUserRepo(Mockery::mock(UserRepositoryInterface::class));

        $response = $this->withHeader('X-Refresh-CSRF', 'valid-csrf-token')
            ->withCookie('refresh_token', 'valid-refresh-token')
            ->withCookie('refresh_csrf', 'valid-csrf-token')
            ->postJson('/api/v1/auth/refresh');

        $response->assertOk()
            ->assertJsonPath('token', 'new-access-token')
            ->assertJsonPath('expires_in', 3600)
            ->assertJsonStructure(['user', 'token', 'expires_in']);

        $this->assertSame('new-refresh-token', $this->getRefreshTokenFromResponse($response));
        $this->assertNotSame('', $this->getRefreshCsrfFromResponse($response));
    }

    public function test_refresh_without_cookie_returns_401(): void
    {
        $tokenRepo = Mockery::mock(TokenRepositoryInterface::class);
        $tokenRepo->shouldNotReceive('consumeRefreshToken');
        $this->bindTokenRepo($tokenRepo);
        $this->bindUserRepo(Mockery::mock(UserRepositoryInterface::class));

        $this->postJson('/api/v1/auth/refresh')->assertUnauthorized();
    }

    public function test_refresh_without_csrf_returns_419(): void
    {
        $tokenRepo = Mockery::mock(TokenRepositoryInterface::class);
        $tokenRepo->shouldNotReceive('consumeRefreshToken');
        $this->bindTokenRepo($tokenRepo);
        $this->bindUserRepo(Mockery::mock(UserRepositoryInterface::class));

        $this->withCookie('refresh_token', 'valid-refresh-token')
            ->postJson('/api/v1/auth/refresh')
            ->assertStatus(419);
    }

    public function test_refresh_with_invalid_token_returns_401(): void
    {
        $tokenRepo = Mockery::mock(TokenRepositoryInterface::class);
        $tokenRepo->shouldReceive('consumeRefreshToken')
            ->once()
            ->with('invalid-refresh-token')
            ->andReturn(null);
        $this->bindTokenRepo($tokenRepo);
        $this->bindUserRepo(Mockery::mock(UserRepositoryInterface::class));

        $this->withHeader('X-Refresh-CSRF', 'valid-csrf-token')
            ->withCookie('refresh_token', 'invalid-refresh-token')
            ->withCookie('refresh_csrf', 'valid-csrf-token')
            ->postJson('/api/v1/auth/refresh')
            ->assertUnauthorized();
    }

    public function test_logout_revokes_tokens_and_forgets_refresh_cookie(): void
    {
        $user = $this->makeAuthUser();

        Sanctum::actingAs($user);

        $tokenRepo = Mockery::mock(TokenRepositoryInterface::class);
        $tokenRepo->shouldReceive('deleteForUser')->with($user)->once();
        $this->bindTokenRepo($tokenRepo);
        $this->bindUserRepo(Mockery::mock(UserRepositoryInterface::class));

        $response = $this
            ->postJson('/api/v1/auth/logout')
            ->assertNoContent();

        $this->assertNotNull(
            $response->getCookie('refresh_token', decrypt: false),
            'refresh_token cookie must be cleared on logout',
        );
        $this->assertNotNull(
            $response->getCookie('refresh_csrf', decrypt: false),
            'refresh_csrf cookie must be cleared on logout',
        );
    }

    public function test_refresh_token_cookie_has_correct_security_attributes(): void
    {
        $user = $this->makeAuthUser();

        $token = Mockery::mock(PersonalAccessToken::class);
        $token->shouldReceive('getAttribute')->with('tokenable')->andReturn($user);

        $tokenRepo = Mockery::mock(TokenRepositoryInterface::class);
        $tokenRepo->shouldReceive('consumeRefreshToken')
            ->once()
            ->with('cookie-attrs-token')
            ->andReturn($token);
        $accessToken2 = Mockery::mock(NewAccessToken::class);
        $accessToken2->plainTextToken = 'new-access-token';
        $refreshToken2 = Mockery::mock(NewAccessToken::class);
        $refreshToken2->plainTextToken = 'new-refresh-token';
        $tokenRepo->shouldReceive('createToken')
            ->with($user, 'access', ['*'], Mockery::type(Carbon::class))
            ->once()
            ->andReturn($accessToken2);
        $tokenRepo->shouldReceive('createToken')
            ->with($user, 'refresh', ['refresh'], Mockery::type(Carbon::class))
            ->once()
            ->andReturn($refreshToken2);
        $this->bindTokenRepo($tokenRepo);
        $this->bindUserRepo(Mockery::mock(UserRepositoryInterface::class));

        $response = $this->withHeader('X-Refresh-CSRF', 'cookie-attrs-csrf')
            ->withCookie('refresh_token', 'cookie-attrs-token')
            ->withCookie('refresh_csrf', 'cookie-attrs-csrf')
            ->postJson('/api/v1/auth/refresh');

        $response->assertOk();

        $cookie = $response->getCookie('refresh_token', decrypt: false);
        $this->assertNotNull($cookie);

        $this->assertTrue($cookie->isHttpOnly(), 'refresh_token must be HttpOnly');
        $this->assertSame(
            'strict',
            strtolower((string) $cookie->getSameSite()),
            'refresh_token must use SameSite=Strict',
        );
        $this->assertSame(
            (bool) config('session.secure'),
            $cookie->isSecure(),
            'Secure flag must follow SESSION_SECURE_COOKIE',
        );
        $this->assertSame('/', $cookie->getPath());

        $csrfCookie = $response->getCookie('refresh_csrf', decrypt: false);
        $this->assertNotNull($csrfCookie);
        $this->assertFalse($csrfCookie->isHttpOnly(), 'refresh_csrf must be readable by frontend JS');
    }
}
