<?php

namespace BoltusLab\QueryDoctor\Tests;

use BoltusLab\QueryDoctor\Support\QueryDoctorConfig;

class QueryDoctorConfigTest extends TestCase
{
    public function test_configuration_service_is_registered(): void
    {
        $first = app(QueryDoctorConfig::class);
        $second = app(QueryDoctorConfig::class);

        $this->assertInstanceOf(QueryDoctorConfig::class, $first);
        $this->assertSame($first, $second);
    }

    public function test_diagnostics_are_disabled_by_default(): void
    {
        config()->set('query-doctor.enabled', false);

        $this->assertFalse(app(QueryDoctorConfig::class)->enabled());
    }

    public function test_diagnostics_can_be_enabled_explicitly(): void
    {
        config()->set('query-doctor.enabled', true);

        $this->assertTrue(app(QueryDoctorConfig::class)->enabled());
    }

    public function test_invalid_query_limit_throws_exception(): void
    {
        config()->set('query-doctor.max_queries_per_context', -1);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid query-doctor.max_queries_per_context');

        app(QueryDoctorConfig::class)->maxQueriesPerContext();
    }

    public function test_default_thresholds(): void
    {
        $config = app(QueryDoctorConfig::class);

        $this->assertSame(200, $config->slowQueryThresholdMs());
        $this->assertSame(5, $config->repeatedQueryThreshold());
        $this->assertSame(1000, $config->maxQueriesPerContext());
    }

    public function test_production_requires_explicit_opt_in(): void
    {
        app()->detectEnvironment(fn() => 'production');

        config()->set('query-doctor.enabled', true);
        config()->set('query-doctor.allow_production', false);

        $this->assertFalse(app(QueryDoctorConfig::class)->enabled());

        config()->set('query-doctor.allow_production', true);

        $this->assertTrue(app(QueryDoctorConfig::class)->enabled());
    }

    public function test_retention_and_sql_length_defaults(): void
    {
        $config = app(QueryDoctorConfig::class);

        $this->assertSame(7, $config->retentionDays());
        $this->assertSame(2048, $config->maxSqlLength());
    }

    public function test_storage_connection_defaults_to_null(): void
    {
        config()->set('query-doctor.storage_connection', null);

        $this->assertNull(app(QueryDoctorConfig::class)->storageConnection());
    }

    public function test_storage_connection_validation(): void
    {
        $config = app(QueryDoctorConfig::class);

        config()->set('query-doctor.storage_connection', 'pgsql');

        $this->assertSame('pgsql', $config->storageConnection());

        config()->set('query-doctor.storage_connection', '');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid query-doctor.storage_connection');

        $config->storageConnection();
    }
}