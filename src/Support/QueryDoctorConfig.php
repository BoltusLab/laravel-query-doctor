<?php

namespace BoltusLab\QueryDoctor\Support;

use InvalidArgumentException;

class QueryDoctorConfig
{
    public function enabled(): bool
    {
        if (config('query-doctor.enabled', false) !== true) {
            return false;
        }

        if (app()->environment('production') && config('query-doctor.allow_production', false) !== true) {
            return false;
        }

        return true;
    }

    public function slowQueryThresholdMs(): int
    {
        return $this->positiveInteger('slow_query_threshold_ms');
    }

    public function repeatedQueryThreshold(): int
    {
        return $this->positiveInteger('repeated_query_threshold');
    }

    public function maxQueriesPerContext(): int
    {
        return $this->positiveInteger('max_queries_per_context');
    }

    public function retentionDays(): int
    {
        return $this->positiveInteger('retention_days');
    }

    public function maxSqlLength(): int
    {
        return $this->positiveInteger('max_sql_length');
    }

    private function positiveInteger(string $key, int $minimum = 1): int
    {
        $value = config("query-doctor.{$key}");

        if (!is_int($value) || $value < $minimum) {
            throw new InvalidArgumentException("Invalid query-doctor.{$key}: expected an integer >= {$minimum}.");
        }

        return $value;
    }

    public function storageConnection(): ?string
    {
        $value = config('query-doctor.storage_connection');

        if ($value === null)
            return null;

        if (!is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException('Invalid query-doctor.storage_connection: expected a non-empty string or null.');
        }

        return $value;
    }
}