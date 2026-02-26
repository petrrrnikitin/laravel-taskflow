<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    #[OA\Post(
        path : '/auth/register',
        summary : 'Register a new user',
        requestBody : new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'secret123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'secret123'),
                ],
            ),
        ),
        tags : ['Auth'],
        responses : [
            new OA\Response(
                response: 201,
                description: 'User registered successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
                        new OA\Property(property: 'token', type: 'string', example: '1|abc123'),
                        new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
                    ],
                ),
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(RegisterDTO::fromRequest($request));
        $csrf = Str::random(40);

        return response()->json([
            'user'       => new UserResource($result->user),
            'token'      => $result->accessToken,
            'expires_in' => $result->expiresIn,
        ], 201)
            ->withCookie($this->refreshCookie($result->refreshToken))
            ->withCookie($this->refreshCsrfCookie($csrf));
    }

    #[OA\Post(
        path : '/auth/login',
        summary : 'Login and get bearer token',
        requestBody : new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
                ],
            ),
        ),
        tags : ['Auth'],
        responses : [
            new OA\Response(
                response: 200,
                description: 'Authenticated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
                        new OA\Property(property: 'token', type: 'string', example: '1|abc123'),
                        new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Invalid credentials'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(LoginDTO::fromRequest($request));
        $csrf = Str::random(40);

        return response()->json([
            'user'       => new UserResource($result->user),
            'token'      => $result->accessToken,
            'expires_in' => $result->expiresIn,
        ])
            ->withCookie($this->refreshCookie($result->refreshToken))
            ->withCookie($this->refreshCsrfCookie($csrf));
    }

    #[OA\Post(
        path: '/auth/logout',
        summary: 'Logout and revoke all tokens',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 204, description: 'Logged out successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $this->authService->logout($user);

        return response()->json(null, 204)
            ->withCookie(Cookie::forget('refresh_token'))
            ->withCookie(Cookie::forget('refresh_csrf'));
    }

    #[OA\Post(
        path: '/auth/refresh',
        summary: 'Refresh access token using HttpOnly cookie',
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'New access token issued',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
                        new OA\Property(property: 'token', type: 'string', example: '1|abc123'),
                        new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function refresh(Request $request): JsonResponse
    {
        $raw = $request->cookie('refresh_token');
        $csrfCookie = $request->cookie('refresh_csrf');
        $csrfHeader = $request->header('X-Refresh-CSRF');

        if (! $raw || ! is_string($raw)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! is_string($csrfCookie) || ! is_string($csrfHeader) || ! hash_equals($csrfCookie, $csrfHeader)) {
            return response()->json(['message' => 'CSRF token mismatch.'], 419);
        }

        try {
            $result = $this->authService->refresh($raw);
        } catch (AuthenticationException) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $csrf = Str::random(40);

        return response()->json([
            'user'       => new UserResource($result->user),
            'token'      => $result->accessToken,
            'expires_in' => $result->expiresIn,
        ])
            ->withCookie($this->refreshCookie($result->refreshToken))
            ->withCookie($this->refreshCsrfCookie($csrf));
    }

    #[OA\Get(
        path: '/auth/me',
        summary: 'Get authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authenticated user data',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResource'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    private function refreshCookie(string $token): SymfonyCookie
    {
        $minutes = (int) config('sanctum.refresh_token_ttl') * 24 * 60;

        return cookie(
            'refresh_token',
            $token,
            $minutes,
            '/',
            null,
            config('session.secure'),
            true,
            false,
            'Strict',
        );
    }

    private function refreshCsrfCookie(string $token): SymfonyCookie
    {
        $minutes = (int) config('sanctum.refresh_token_ttl') * 24 * 60;

        return cookie(
            'refresh_csrf',
            $token,
            $minutes,
            '/',
            null,
            config('session.secure'),
            false,
            false,
            'Strict',
        );
    }
}
