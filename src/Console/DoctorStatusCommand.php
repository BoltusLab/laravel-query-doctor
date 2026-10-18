<?php

namespace BoltusLab\QueryDoctor\Console;

use Illuminate\Console\Command;

class DoctorStatusCommand extends Command
{
    protected $signature = 'query-doctor:status';

    protected $description = 'Display Laravel Query Doctor status';

    public function handle(): int
    {
        $enabled = config('query-doctor.enabled', false);
        $this->components->info('Laravel Query Doctor');
        $this->line('Diagnostics: ' . ($enabled ? 'Enabled' : 'Disabled'));
        $this->line('Slow query threshold: ' . config('query-doctor.slow_query_threshold_ms', 200) . ' ms');
        return self::SUCCESS;
    }
}
