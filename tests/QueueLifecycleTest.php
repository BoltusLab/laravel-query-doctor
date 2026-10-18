<?php

namespace BoltusLab\QueryDoctor\Tests;

use ArrayObject;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Events\QueryExecuted;
use BoltusLab\QueryDoctor\Collectors\QueryCollector;
use BoltusLab\QueryDoctor\Tests\Fixtures\QueryDoctorTestJob;

class QueueLifecycleTest extends TestCase
{
    public function test_collector_resets_before_queue_job_processing(): void
    {
        $collector = app(QueryCollector::class);

        config()->set('query-doctor.enabled', true);

        $collector->record(new QueryExecuted('SELECT 1', [], 10, app('db')->connection()));

        $this->assertCount(1, $collector->all());

        $job = $this->createMock(Job::class);

        $job->method('payload')->willReturn([
            'uuid' => 'query-doctor-test-job',
            'displayName' => 'QueryDoctorTestJob',
        ]);

        event(new JobProcessing('sync', $job, []));

        $this->assertCount(0, $collector->all());
        $this->assertFalse($collector->isTruncated());
    }

    public function test_consecutive_queued_jobs_do_not_share_queries(): void
    {
        config()->set('query-doctor.enabled', true);
        config()->set('queue.default', 'sync');

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        DB::purge('sqlite');

        $this->app->instance('query-doctor.test.counts', new ArrayObject());

        $makeJob = fn() => new class implements ShouldQueue {
            public function handle(): void
                {
                    DB::select('SELECT 1');

                    app('query-doctor.test.counts')->append(count(app(QueryCollector::class)->all()));
                }
            };

        Queue::connection('sync')->push(new QueryDoctorTestJob());
        Queue::connection('sync')->push(new QueryDoctorTestJob());

        $this->assertSame([1, 1], app('query-doctor.test.counts')->getArrayCopy());
    }
}