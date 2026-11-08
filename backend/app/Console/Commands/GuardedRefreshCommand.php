<?php

namespace App\Console\Commands;

use Illuminate\Console\Input\InputInterface;
use Illuminate\Console\Output\OutputInterface;
use Illuminate\Foundation\Console\RefreshCommand;

class GuardedRefreshCommand extends RefreshCommand
{
    protected $signature = 'migrate:refresh';

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $connectionName = $input->getOption('database')
            ?? config('database.default');

        if (! $connectionName || ! is_string($connectionName)) {
            throw new \RuntimeException(
                'Cannot resolve database connection for migrate:refresh. Destructive migration blocked.'
            );
        }

        $databaseName = config("database.connections.{$connectionName}.database");

        if (! $databaseName || ! is_string($databaseName)) {
            throw new \RuntimeException(
                'Cannot determine target database name for migrate:refresh. Destructive migration blocked.'
            );
        }

        if (strtolower(trim($databaseName)) === 'school_lms') {
            throw new \RuntimeException(
                'Destructive migration "migrate:refresh" is prohibited on database "' . $databaseName . '".'
            );
        }

        return parent::run($input, $output);
    }
}
