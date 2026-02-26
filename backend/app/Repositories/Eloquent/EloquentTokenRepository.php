<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\TokenRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class EloquentTokenRepository implements TokenRepositoryInterface
{
    public function consumeRefreshToken(string $plainTextToken): ?PersonalAccessToken
    {
        return DB::transaction(static function () use ($plainTextToken): ?PersonalAccessToken {
            if (! str_contains($plainTextToken, '|')) {
                return null;
            }

            [$id, $token] = explode('|', $plainTextToken, 2);

            /** @var PersonalAccessToken|null $model */
            $model = PersonalAccessToken::where('id', (int) $id)
                ->where('name', 'refresh')
                ->lockForUpdate()
                ->first();

            if (! $model || ! hash_equals($model->token, hash('sha256', $token))) {
                return null;
            }

            if ($model->expires_at?->isPast()) {
                return null;
            }

            $model->load('tokenable');
            $model->delete();

            return $model;
        });
    }
}
