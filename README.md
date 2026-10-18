# Laravel Query Doctor

**Find slow queries. Detect repeated database operations. Investigate probable N+1 problems.**

Laravel Query Doctor is an open-source database performance diagnostic package for Laravel applications.

It is designed to help developers identify inefficient database queries, understand their performance impact, and investigate potential optimizations through actionable, evidence-based reports.

The package integrates directly into existing Laravel applications and aims to provide lightweight diagnostics without requiring a separate monitoring server or web dashboard.

> **Development Status:** 🚧 Under active development. The first public release (v0.1) is being planned and implemented. Installation commands, configuration options, and CLI examples below describe the intended interface and may change before release.

## Why Laravel Query Doctor?

Database performance problems are not always obvious.

An application might execute hundreds of small queries, repeatedly fetch the same records, or perform expensive operations that gradually affect response times.

Laravel Query Doctor aims to make these problems easier to investigate by providing:

- **Slow Query Detection** — Identify database operations exceeding configurable execution-time thresholds.
- **Repeated Query Detection** — Group similar SQL queries and measure their cumulative cost.
- **Probable N+1 Detection** — Highlight suspicious query patterns using supporting evidence rather than repetition alone.
- **SQL Fingerprinting** — Normalize query structures to identify related operations.
- **Context-Aware Diagnostics** — Analyze HTTP requests, Artisan commands, and queue jobs independently.
- **Persistent Diagnostics** — Store sanitized diagnostic records in a configurable database connection.
- **Developer-Friendly Reports** — Review findings through Artisan or export JSON and Markdown reports.
- **Privacy-Conscious Collection** — Avoid persisting raw query bindings and sanitize SQL before storage.

## Planned Compatibility

| Component | Target |
|---|---|
| Laravel | 10, 11, 12, 13 |
| PHP | Compatible versions for each supported Laravel release |
| Database | MySQL and PostgreSQL |
| Installation | Composer |
| License | MIT |

Compatibility will be claimed only for framework, PHP, and database combinations validated by automated tests.

## Installation

*Planned for the first public release.*

```bash
composer require boltuslab/laravel-query-doctor
```

The package is intended to support Laravel package discovery.

Database migrations will require an explicit developer action. They will not be executed automatically.

The final migration installation command will be documented when the package foundation is implemented.

## Configuration

Laravel Query Doctor will support configurable collection, detection, storage, and retention settings.

### Planned defaults

| Setting | Default |
|---|---|
| Slow query threshold | 200 ms |
| Repeated query threshold | 5 occurrences |
| Diagnostic retention | 7 days |
| Maximum queries per context | 1,000 |
| Maximum stored SQL template length | 2,048 characters |
| Storage connection | Configurable Laravel database connection |
| Production collection | Disabled by default |
| N+1 classification | Conservative, evidence-based |

Slow queries are identified when execution time is **strictly greater than** the configured threshold.

Repeated queries are grouped within the same execution context and database connection.

Production diagnostics will require explicit opt-in and bounded collection limits.

The final configuration keys and environment variables will be published alongside the implementation.

## How It Will Work

```text
Laravel Application
        |
        v
Database Query Events
        |
        v
Query Collector
        |
        v
SQL Sanitization & Fingerprinting
        |
        v
Query Aggregation
        |
        v
Diagnostic Detection Engine
        |
        +---- Slow Queries
        |
        +---- Repeated Queries
        |
        +---- Probable N+1 Queries
        |
        v
Database Persistence
        |
        v
Artisan Report Generator
        |
        +---- Terminal
        +---- JSON
        +---- Markdown
```

Query collection will be bounded, and the package's own persistence queries must be excluded from diagnostics to prevent recursive capture.

## Reporting

The package will provide an Artisan-based reporting interface.

The following is an illustrative example, not output from an implemented command.

```text
Laravel Query Doctor
────────────────────────────────────────

Execution Context: HTTP Request
Queries Captured: 42
Total Query Time: 892 ms

FINDINGS

[HIGH] Slow Query
Duration: 384 ms
Threshold: 200 ms

SQL:
SELECT * FROM orders WHERE user_id = ?

Suggestion:
Investigate the query execution plan
and relevant indexes.

────────────────────────────────────────

[MEDIUM] Repeated Query
Occurrences: 12
Cumulative Time: 246 ms

SQL:
SELECT * FROM products WHERE id = ?

Suggestion:
Review repeated lookups and consider
appropriate eager loading or batching.

────────────────────────────────────────

[INFO] Probable N+1 Pattern

Evidence:
Repeated query fingerprint within
the same execution context.

Note:
Additional contextual evidence is
required before classifying an N+1
pattern. Repetition alone is not proof.
```

Reports will distinguish between no captured data, no findings, incomplete captures, and operational errors.

### Export formats

Planned reporting formats include:

- **Terminal:** Readable Artisan output for interactive investigation.
- **JSON:** Schema-versioned, machine-readable diagnostic records.
- **Markdown:** Portable reports for code reviews, issue tracking, and documentation.

## Security and Privacy

Security is a core design requirement.

Laravel Query Doctor is intended to:

- Sanitize SQL templates before persistence.
- Avoid storing raw query bindings, passwords, tokens, or request payloads.
- Limit captured queries and stored SQL lengths.
- Exclude its own database writes from collection.
- Prevent diagnostic failures from changing application outcomes.
- Support configurable data retention and bounded cleanup.
- Disable production collection unless explicitly enabled.

Diagnostic data may still reveal application structure and usage patterns. Database permissions, report access, and retention settings should therefore be managed carefully.

## Performance Goals

The package targets **less than 5% diagnostic overhead** in documented representative benchmarks.

This is a development target, not a verified performance guarantee.

Benchmarks will compare enabled and disabled diagnostics across representative HTTP, Artisan, and queue workloads. Results and deviations will be published when testing is complete.

## Roadmap

### v0.1 — Core Diagnostics

- [ ] Composer package foundation and Laravel integration
- [ ] HTTP, Artisan, and queue query collection
- [ ] SQL sanitization and fingerprinting
- [ ] Slow query detection
- [ ] Repeated query detection
- [ ] Conservative probable N+1 detection
- [ ] Database-backed diagnostic persistence
- [ ] Configurable retention and cleanup
- [ ] Artisan terminal reporting
- [ ] JSON and Markdown exports
- [ ] Automated compatibility, security, and performance tests

### v0.2 — Query Intelligence

- [ ] Opt-in, non-mutating execution-plan analysis
- [ ] MySQL and PostgreSQL EXPLAIN adapters
- [ ] Advisory index recommendations

### v0.3 — Performance Regression Analysis

- [ ] Diagnostic baselines
- [ ] Comparison of query metrics across runs
- [ ] Performance regression indicators

### v1.0 — Stable Release

- [ ] Stable documented public APIs
- [ ] Validated compatibility matrix
- [ ] Comprehensive documentation
- [ ] Release and upgrade guidance

## What Laravel Query Doctor Is Not

Laravel Query Doctor is not intended to replace full application observability platforms or database monitoring infrastructure.

The initial release will not include a web dashboard, hosted telemetry service, automatic SQL rewriting, automatic index creation, or automatic database schema modifications.

Its primary purpose is to help Laravel developers **investigate database performance using actionable evidence**.

## Contributing

Contributions, bug reports, documentation improvements, and feature suggestions will be welcome as the project develops.

Contribution guidelines, coding standards, and test instructions will be added as the repository is established.

## License

Laravel Query Doctor is intended to be released under the **MIT License**.

See the `LICENSE` file once it has been added to the repository.

---

**Laravel Query Doctor** — Better visibility into Laravel database performance.
