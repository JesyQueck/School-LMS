<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailtrapEmail extends Command
{
    protected $signature = 'mail:test-mailtrap {email}';

    protected $description = 'Send a test email through the configured SMTP transport';

    public function handle(): int
    {
        $email = $this->argument('email');

        Mail::raw(
            'This is a test email from the School-LMS development environment.',
            function ($message) use ($email) {
                $message->to($email)
                    ->subject('School-LMS Mailtrap Test');
            }
        );

        $this->info('Test email sent to the configured mail transport.');

        return self::SUCCESS;
    }
}