<?php

namespace BoltusLab\QueryDoctor\Tests\Fixtures;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use BoltusLab\QueryDoctor\Collectors\QueryCollector;

class QueryDoctorTestJob implements ShouldQueue
{
    public function handle(): void
    {
        DB::select('SELECT 1');

        app('query-doctor.test.counts')->append(count(app(QueryCollector::class)->all()));
    }
}