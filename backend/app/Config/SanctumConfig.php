<?php

namespace App\Config;

final readonly class SanctumConfig
{
    public function __construct(
        public int $accessTokenTtl,
        public int $refreshTokenTtl,
    ) {
    }
}
