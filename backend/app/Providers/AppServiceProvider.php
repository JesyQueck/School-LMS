<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Database\Console\Migrations\RollbackCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Input\InputOption;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('login', function ($request) {
            $identifier = $request->input('identifier', $request->input('email', ''));
            $identifier = is_string($identifier) ? mb_strtolower(trim($identifier)) : '';

            return Limit::perMinute(5)->by($identifier.$request->ip())
                ->response(function () {
                    $retryAfter = 60;

                    return response()->make(
                        __('auth.throttle', ['seconds' => $retryAfter]),
                        429,
                        ['Retry-After' => $retryAfter]
                    );
                });
        });

        Authenticate::redirectUsing(function ($request) {
            return $request->expectsJson() ? null : (Route::has('login') ? route('login') : '/login');
        });

        // Blade components are auto-discovered in Laravel 13+
        // No explicit registration needed for view components

        $this->registerDestructiveMigrationGuards();
    }

    protected function registerDestructiveMigrationGuards(): void
    {
        $destructiveCommands = [
            'migrate:rollback' => RollbackCommand::class,
            'migrate:reset' => ResetCommand::class,
            'migrate:fresh' => FreshCommand::class,
            'migrate:refresh' => RefreshCommand::class,
        ];

        foreach ($destructiveCommands as $commandName => $originalCommandClass) {
            $command = Artisan::command($commandName, function () use ($commandName, $originalCommandClass): int {
                $connectionName = $this->input->getOption('database')
                    ?? config('database.default');

                if (! $connectionName || ! is_string($connectionName)) {
                    throw new \RuntimeException(
                        'Cannot resolve database connection for ' . $commandName . '. Destructive migration blocked.'
                    );
                }

                $databaseName = config("database.connections.{$connectionName}.database");

                if (! $databaseName || ! is_string($databaseName)) {
                    throw new \RuntimeException(
                        'Cannot determine target database name for ' . $commandName . '. Destructive migration blocked.'
                    );
                }

                if (strtolower(trim($databaseName)) === 'school_lms') {
                    throw new \RuntimeException(
                        'Destructive migration "' . $commandName .
                        '" is prohibited on database "' . $databaseName . '".'
                    );
                }

                $originalCommand = app($originalCommandClass);
                $originalCommand->setLaravel(app());
                return $originalCommand->run($this->input, $this->output);
            });

            // Add the --database option to the ClosureCommand so getOption('database') works
            $command->getDefinition()->addOption(new InputOption('database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'));
        }
    }
}
