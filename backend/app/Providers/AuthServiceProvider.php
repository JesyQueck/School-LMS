<?php

namespace App\Providers;

use App\Models\ReportCard;
use App\Models\Student;
use App\Models\User;
use App\Policies\ReportCardPolicy;
use App\Policies\StudentPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Student::class => StudentPolicy::class,
        User::class => UserPolicy::class,
        ReportCard::class => ReportCardPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
