<?php

namespace App\Providers;

use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\TimeSchedule;
use App\Policies\ClassRoomPolicy;
use App\Policies\SchoolYearPolicy;
use App\Policies\StudentPolicy;
use App\Policies\TimeSchedulePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Student::class => StudentPolicy::class,
        ClassRoom::class => ClassRoomPolicy::class,
        TimeSchedule::class => TimeSchedulePolicy::class,
        SchoolYear::class => SchoolYearPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }

    /**
     * Register the application's policies.
     */
    public function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
