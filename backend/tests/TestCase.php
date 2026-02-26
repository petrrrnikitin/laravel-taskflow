<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $connection = (string) config('database.default');
        $database = config("database.connections.{$connection}.database");
        $database = is_string($database) ? $database : '';

        $isSafeSqlite = $connection === 'sqlite' && in_array($database, [':memory:', database_path('database.sqlite')], true);
        $isExplicitTestDb = str_ends_with($database, '_test') || str_ends_with($database, '_testing');

        if (! app()->environment('testing') || (! $isSafeSqlite && ! $isExplicitTestDb)) {
            throw new RuntimeException(sprintf(
                'Unsafe test database configuration: APP_ENV=%s, DB_CONNECTION=%s, DB_DATABASE=%s. '.
                'Use sqlite (:memory:) or a dedicated *_test database.',
                (string) config('app.env'),
                $connection,
                $database === '' ? '(empty)' : $database,
            ));
        }
    }
}
