<?php

namespace BoltusLab\QueryDoctor\Tests;

use BoltusLab\QueryDoctor\Collectors\QueryCollector;
use BoltusLab\QueryDoctor\Http\Middleware\ResetQueryCollector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class HttpLifecycleTest extends TestCase
{
    private function configureSqlite(): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        DB::purge('sqlite');
    }

    public function test_http_request_captures_database_query(): void
    {
        config()->set('query-doctor.enabled', true);

        $this->configureSqlite();

        Route::get('/query-doctor-test', function () {
            DB::select('SELECT 1');

            return response()->json([
                'count' => count(app(QueryCollector::class)->all()),
            ]);
        });

        $response = $this->get('/query-doctor-test');

        $response->assertOk();
        $response->assertJsonPath('count', 1);
    }

    public function test_consecutive_http_requests_do_not_share_queries(): void
    {
        config()->set('query-doctor.enabled', true);

        $this->configureSqlite();

        config()->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        Route::middleware('web')
            ->get('/query-doctor-isolation', function () {
                DB::select('SELECT 1');

                return response()->json([
                    'count' => count(app(QueryCollector::class)->all()),
                ]);
            });

        $firstResponse = $this->get('/query-doctor-isolation');
        $secondResponse = $this->get('/query-doctor-isolation');

        $firstResponse->assertOk();
        $secondResponse->assertOk();

        $firstResponse->assertJsonPath('count', 1);
        $secondResponse->assertJsonPath('count', 1);
    }

    public function test_web_group_contains_reset_middleware_after_kernel_initialization(): void
    {
        $this->app->make(\Illuminate\Contracts\Http\Kernel::class);

        $webMiddleware = $this->app['router']->getMiddlewareGroups()['web'] ?? [];

        $this->assertContains(ResetQueryCollector::class, $webMiddleware);
    }

    public function test_consecutive_api_requests_do_not_share_queries(): void
    {
        config()->set('query-doctor.enabled', true);

        $this->configureSqlite();

        Route::middleware('api')
            ->get('/api/query-doctor-isolation', function () {
                DB::select('SELECT 1');

                return response()->json([
                    'count' => count(app(QueryCollector::class)->all()),
                ]);
            });

        $firstResponse = $this->get('/api/query-doctor-isolation');
        $secondResponse = $this->get('/api/query-doctor-isolation');

        $firstResponse->assertOk();
        $secondResponse->assertOk();

        $firstResponse->assertJsonPath('count', 1);
        $secondResponse->assertJsonPath('count', 1);
    }
}
