<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function refreshInDatabase()
    {
        if (! $this->refreshInDatabaseState) {
            $connectionName = config('database.default');

            if ($connectionName === 'mysql') {
                throw new \RuntimeException(
                    'Tests are blocked because the default test database connection is MySQL.'
                );
            }
            $this->artisan('migrate:fresh', ['--database' => $connectionName]);
            $this->refreshInDatabaseState = true;
        }
    }
}