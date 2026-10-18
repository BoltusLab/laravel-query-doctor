<?php

namespace BoltusLab\QueryDoctor\Tests;

use BoltusLab\QueryDoctor\QueryDoctorServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            QueryDoctorServiceProvider::class,
        ];
    }
}
