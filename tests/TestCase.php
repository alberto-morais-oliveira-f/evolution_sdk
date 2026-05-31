<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Tests;

use Am2tec\EvolutionSdk\Providers\EvolutionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [EvolutionServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('evolution.url', 'http://localhost:8080');
        $app['config']->set('evolution.key', 'test-api-key');
        $app['config']->set('evolution.instance', 'test-instance');
        $app['config']->set('evolution.timeout', 30);
    }
}
