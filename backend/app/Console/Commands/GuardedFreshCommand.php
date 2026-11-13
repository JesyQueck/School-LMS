<?php

namespace App\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Database\Console\Migrations\FreshCommand;

class GuardedFreshCommand extends FreshCommand
{
    public function run(InputInterface $input, OutputInterface $output): int
    {
        // Bind the command definition before reading options.
        $this->mergeApplicationDefinition();
        $input->bind($this->getDefinition());

        $connectionName = $input->getOption('database');

        if ($connectionName === null) {
            $connectionName = config('database.default');
        }

        if (! $connectionName || ! is_string($connectionName)) {
            throw new \RuntimeException(
                'Cannot resolve database connection for migrate:fresh. Destructive migration blocked.'
            );
        }

        $databaseName = config("database.connections.{$connectionName}.database");

        if (! $databaseName || ! is_string($databaseName)) {
            throw new \RuntimeException(
                'Cannot determine target database name for migrate:fresh. Destructive migration blocked.'
            );
        }

        if (strtolower(trim($databaseName)) === 'school_lms') {
            throw new \RuntimeException(
                'Destructive migration "migrate:fresh" is prohibited on database "' . $databaseName . '".'
            );
        }

        return parent::run($input, $output);
    }
}
