<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DestructiveMigrationGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.database', 'school_lms');
        Config::set('database.connections.sqlite.database', ':memory:');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Config::set('database.connections.mysql.database', 'school_lms');
        Config::set('database.connections.sqlite.database', ':memory:');
    }

    public function test_migrate_rollback_blocked_on_school_lms(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Destructive migration "migrate:rollback" is prohibited on database "school_lms".'
        );

        Artisan::call('migrate:rollback');
    }

    public function test_migrate_reset_blocked_on_school_lms(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Destructive migration "migrate:reset" is prohibited on database "school_lms".'
        );

        Artisan::call('migrate:reset');
    }

    public function test_migrate_fresh_blocked_on_school_lms(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Destructive migration "migrate:fresh" is prohibited on database "school_lms".'
        );

        Artisan::call('migrate:fresh');
    }

    public function test_migrate_refresh_blocked_on_school_lms(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Destructive migration "migrate:refresh" is prohibited on database "school_lms".'
        );

        Artisan::call('migrate:refresh');
    }

    public function test_normal_migrate_permitted_on_school_lms(): void
    {
        // Verify the ordinary migrate command remains Laravel's native command.
        // Do not execute it here: pending migrations must not run against school_lms.
        $command = Artisan::all()['migrate'] ?? null;

        $this->assertInstanceOf(
            \Illuminate\Database\Console\Migrations\MigrateCommand::class,
            $command
        );
    }
    public function test_destructive_commands_allowed_on_sqlite(): void
    {
        Config::set('database.default', 'sqlite');

        foreach (['migrate:rollback', 'migrate:reset', 'migrate:fresh', 'migrate:refresh'] as $commandName) {
            try {
                Artisan::call($commandName, ['--database' => 'sqlite']);
                $this->assertTrue(true, "{$commandName} was not blocked on SQLite.");
            } catch (\Throwable $e) {
                $this->assertStringNotContainsString(
                    'Destructive migration',
                    $e->getMessage(),
                    "{$commandName} was incorrectly blocked on SQLite."
                );
            }
        }
    }
    public function test_unknown_database_fails_closed(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Cannot determine target database name for migrate:rollback. Destructive migration blocked.'
        );

        Artisan::call('migrate:rollback', ['--database' => 'nonexistent']);
    }

    public function test_missing_database_config_fails_closed(): void
    {
        Config::set('database.connections.mysql.database', null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Cannot determine target database name for migrate:rollback. Destructive migration blocked.'
        );

        Artisan::call('migrate:rollback');
    }

    public function test_empty_database_config_fails_closed(): void
    {
        Config::set('database.connections.mysql.database', '');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Cannot determine target database name for migrate:rollback. Destructive migration blocked.'
        );

        Artisan::call('migrate:rollback');
    }

    public function test_case_insensitive_database_match(): void
    {
        Config::set('database.connections.mysql.database', 'SCHOOL_LMS');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Destructive migration "migrate:rollback" is prohibited on database "SCHOOL_LMS".'
        );

        Artisan::call('migrate:rollback');
    }

    public function test_url_based_database_configuration_blocked(): void
    {
        Config::set('database.connections.mysql.url', 'mysql://root@127.0.0.1:3306/school_lms');
        Config::set('database.connections.mysql.database', null);

        $this->expectException(\RuntimeException::class);

        Artisan::call('migrate:rollback');
    }
}
