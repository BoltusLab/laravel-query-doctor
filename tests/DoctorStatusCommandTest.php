<?php

namespace BoltusLab\QueryDoctor\Tests;

class DoctorStatusCommandTest extends TestCase
{
    public function test_status_command_displays_default_configuration(): void
    {
        $this->artisan('query-doctor:status')
            ->expectsOutputToContain('Diagnostics: Disabled')
            ->expectsOutputToContain('Slow query threshold: 200 ms')
            ->assertExitCode(0);
    }

    public function test_status_command_reflects_enabled_configuration(): void
    {
        config()->set('query-doctor.enabled', true);

        $this->artisan('query-doctor:status')
            ->expectsOutputToContain('Diagnostics: Enabled')
            ->assertExitCode(0);
    }
}
