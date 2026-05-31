<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Providers;

use Am2tec\EvolutionSdk\EvolutionClient;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;

class EvolutionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/evolution.php', 'evolution');

        $this->app->singleton('evolution', function ($app) {
            return new EvolutionClient(
                http: $app->make(HttpFactory::class),
                baseUrl: config('evolution.url'),
                apiKey: config('evolution.key'),
                timeout: (int) config('evolution.timeout', 30),
            );
        });

        $this->app->alias('evolution', EvolutionClient::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/evolution.php' => config_path('evolution.php'),
            ], 'evolution-config');
        }
    }
}
