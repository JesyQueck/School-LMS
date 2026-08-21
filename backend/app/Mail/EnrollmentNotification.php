<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnrollmentNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public User $user;

    public string $temporaryPassword;

    public string $relatedStudent;

    public string $schoolName;

    public function __construct(User $user, string $temporaryPassword, string $relatedStudent = '', string $schoolName = '')
    {
        $this->user = $user;
        $this->temporaryPassword = $temporaryPassword;
        $this->relatedStudent = $relatedStudent;
        $this->schoolName = $schoolName ?: config('school.name', 'School');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->schoolName} — Account Created: Please Set Your Password",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.enrollment-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
