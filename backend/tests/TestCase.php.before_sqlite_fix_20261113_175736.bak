<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function refreshInDatabase()
    {
        if (! $this->refreshInDatabaseState) {
            $connectionName = config('database.default');
            foreach (config('database.connections') as $name => $conn) {
                if ($conn['database'] ?? null !== config('database.connections.mysql.database')) {
                    $connectionName = $name;
                    break;
                }
            }
            $this->artisan('migrate:fresh', ['--database' => $connectionName]);
            $this->refreshInDatabaseState = true;
        }
    }
}