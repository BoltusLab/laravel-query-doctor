<?php

namespace BoltusLab\QueryDoctor;

use Illuminate\Support\ServiceProvider;

class QueryDoctorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/query-doctor.php', 'query-doctor');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\DoctorStatusCommand::class,
            ]);
        }
    }
}