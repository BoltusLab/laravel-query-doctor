<?php

namespace BoltusLab\QueryDoctor\Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use BoltusLab\QueryDoctor\Collectors\QueryCollector;

class QueryCollectorTest extends TestCase
{
    public function test_collector_is_registered(): void
    {
        $collector = app(QueryCollector::class);

        $this->assertInstanceOf(QueryCollector::class, $collector);
    }

    public function test_disabled_collector_does_not_record_queries(): void
    {
        config()->set('query-doctor.enabled', false);

        $collector = app(QueryCollector::class);

        $collector->record($this->fakeQueryEvent());

        $this->assertCount(0, $collector->all());
    }

    public function test_enabled_collector_records_query_metadata(): void
    {
        config()->set('query-doctor.enabled', true);

        $collector = app(QueryCollector::class);

        $collector->record($this->fakeQueryEvent());

        $this->assertCount(1, $collector->all());

        $this->assertSame(12.5, $collector->all()[0]['duration_ms']);
        $this->assertSame('testing', $collector->all()[0]['connection']);
    }

    private function fakeQueryEvent(): QueryExecuted
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('getName')->willReturn('testing');

        return new QueryExecuted(
            'SELECT * FROM users WHERE email = ?',
            ['secret@example.com'],
            12.5,
            $connection
        );
    }

    public function test_collector_respects_query_limit(): void
    {
        config()->set('query-doctor.enabled', true);
        config()->set('query-doctor.max_queries_per_context', 2);

        $collector = app(QueryCollector::class);

        for ($i = 0; $i < 5; $i++) {
            $collector->record($this->fakeQueryEvent());
        }

        $this->assertCount(2, $collector->all());
        $this->assertTrue($collector->isTruncated());
    }

    public function test_collector_can_be_reset(): void
    {
        config()->set('query-doctor.enabled', true);

        $collector = app(QueryCollector::class);

        $collector->record($this->fakeQueryEvent());

        $this->assertCount(1, $collector->all());

        $collector->reset();

        $this->assertCount(0, $collector->all());
        $this->assertFalse($collector->isTruncated());
    }

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
    public function test_listener_captures_real_database_query(): void
    {
        config()->set('query-doctor.enabled', true);

        $this->configureSqlite();

        $collector = app(QueryCollector::class);
        $collector->reset();

        DB::connection('sqlite')->select('SELECT 1 AS value');

        $queries = $collector->all();

        $this->assertCount(1, $queries);
        $this->assertSame('sqlite', $queries[0]['connection']);
        $this->assertIsNumeric($queries[0]['duration_ms']);
    }

    public function test_listener_ignores_queries_when_disabled(): void
    {
        config()->set('query-doctor.enabled', false);

        $this->configureSqlite();

        $collector = app(QueryCollector::class);
        $collector->reset();

        DB::connection('sqlite')->select('SELECT 2 AS value');

        $this->assertCount(0, $collector->all());
    }

    public function test_collector_is_not_truncated_at_exact_limit(): void
    {
        config()->set('query-doctor.enabled', true);
        config()->set('query-doctor.max_queries_per_context', 2);

        $collector = app(QueryCollector::class);

        $collector->record($this->fakeQueryEvent());
        $collector->record($this->fakeQueryEvent());

        $this->assertCount(2, $collector->all());
        $this->assertFalse($collector->isTruncated());
    }

    public function test_listener_ignores_queries_in_production_without_opt_in(): void
    {
        config()->set('query-doctor.enabled', true);
        config()->set('query-doctor.allow_production', false);

        $this->app['env'] = 'production';

        $this->configureSqlite();

        $collector = app(QueryCollector::class);
        $collector->reset();

        DB::connection('sqlite')->select('SELECT 3 AS value');

        $this->assertCount(0, $collector->all());
    }

    public function test_listener_captures_queries_in_production_with_explicit_opt_in(): void
    {
        config()->set('query-doctor.enabled', true);
        config()->set('query-doctor.allow_production', true);

        $this->app['env'] = 'production';

        $this->configureSqlite();

        $collector = app(QueryCollector::class);
        $collector->reset();

        DB::connection('sqlite')->select('SELECT 4 AS value');

        $this->assertCount(1, $collector->all());
        $this->assertSame('sqlite', $collector->all()[0]['connection']);
    }

    public function test_collector_is_isolated_between_container_scopes(): void
    {
        config()->set('query-doctor.enabled', true);

        $firstCollector = app(QueryCollector::class);
        $firstCollector->record($this->fakeQueryEvent());

        $this->assertCount(1, $firstCollector->all());

        $this->app->forgetScopedInstances();

        $secondCollector = app(QueryCollector::class);

        $this->assertNotSame($firstCollector, $secondCollector);
        $this->assertCount(0, $secondCollector->all());
    }

    public function test_listener_uses_fresh_collector_after_scope_reset(): void
    {
        config()->set('query-doctor.enabled', true);

        $this->configureSqlite();

        $firstCollector = app(QueryCollector::class);

        DB::connection('sqlite')->select('SELECT 1');

        $this->assertCount(1, $firstCollector->all());

        $this->app->forgetScopedInstances();

        $secondCollector = app(QueryCollector::class);

        DB::connection('sqlite')->select('SELECT 2');

        $this->assertNotSame($firstCollector, $secondCollector);
        $this->assertCount(1, $secondCollector->all());
        $this->assertCount(1, $firstCollector->all());
    }
}