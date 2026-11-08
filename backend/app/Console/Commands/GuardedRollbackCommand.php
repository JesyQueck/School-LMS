<?php

namespace App\Console\Commands;

use Illuminate\Console\Input\InputInterface;
use Illuminate\Console\Output\OutputInterface;
use Illuminate\Foundation\Console\RollbackCommand;

class GuardedRollbackCommand extends RollbackCommand
{
    protected $signature = 'migrate:rollback';

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $connectionName = $input->getOption('database')
            ?? config('database.default');

        if (! $connectionName || ! is_string($connectionName)) {
            throw new \RuntimeException(
                'Cannot resolve database connection for migrate:rollback. Destructive migration blocked.'
            );
        }

        $databaseName = config("database.connections.{$connectionName}.database");

        if (! $databaseName || ! is_string($databaseName)) {
            throw new \RuntimeException(
                'Cannot determine target database name for migrate:rollback. Destructive migration blocked.'
            );
        }

        if (strtolower(trim($databaseName)) === 'school_lms') {
            throw new \RuntimeException(
                'Destructive migration "migrate:rollback" is prohibited on database "' . $databaseName . '".'
            );
        }

        return parent::run($input, $output);
    }
}
