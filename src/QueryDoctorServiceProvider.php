<?php

namespace BoltusLab\QueryDoctor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use BoltusLab\QueryDoctor\Support\QueryDoctorConfig;
use BoltusLab\QueryDoctor\Collectors\QueryCollector;
use BoltusLab\QueryDoctor\Console\DoctorStatusCommand;
use BoltusLab\QueryDoctor\Http\Middleware\ResetQueryCollector;

class QueryDoctorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/query-doctor.php', 'query-doctor');
        $this->app->singleton(QueryDoctorConfig::class, fn($app) => new QueryDoctorConfig());
        $this->app->scoped(QueryCollector::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DoctorStatusCommand::class,
            ]);
        }

        DB::listen(function (QueryExecuted $event): void {
            $this->app->make(QueryCollector::class)->record($event);
        });

        $this->app->afterResolving(HttpKernel::class, function (HttpKernel $kernel): void {
            $this->app['router']->pushMiddlewareToGroup('web', ResetQueryCollector::class);
            $this->app['router']->pushMiddlewareToGroup('api', ResetQueryCollector::class);
        });

        Event::listen(JobProcessing::class, function (JobProcessing $event): void {
            $this->app->make(QueryCollector::class)->reset();
        });
    }
}