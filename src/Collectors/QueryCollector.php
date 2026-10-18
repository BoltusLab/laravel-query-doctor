<?php

namespace BoltusLab\QueryDoctor\Collectors;

use Illuminate\Database\Events\QueryExecuted;
use BoltusLab\QueryDoctor\Support\QueryDoctorConfig;

class QueryCollector
{
    private array $queries = [];
    private bool $truncated = false;

    public function __construct(private readonly QueryDoctorConfig $config)
    {
    }

    public function record(QueryExecuted $event): void
    {
        if (!$this->config->enabled()) {
            return;
        }

        if (count($this->queries) >= $this->config->maxQueriesPerContext()) {
            $this->truncated = true;
            return;
        }

        $this->queries[] = [
            'duration_ms' => $event->time,
            'connection' => $event->connectionName,
        ];
    }

    public function all(): array
    {
        return $this->queries;
    }

    public function reset(): void
    {
        $this->queries = [];
        $this->truncated = false;
    }

    public function isTruncated(): bool
    {
        return $this->truncated;
    }
}