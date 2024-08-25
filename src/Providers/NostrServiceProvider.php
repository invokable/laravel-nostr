<?php

declare(strict_types=1);

namespace Revolution\Nostr\Providers;

use Illuminate\Support\ServiceProvider;
use Revolution\Nostr\Console\NostrServe;
use Revolution\Nostr\Console\SocialTestCommand;
use Revolution\Nostr\Social\SocialClient;

class NostrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/nostr.php',
            'nostr'
        );

        $this->app->scoped(SocialClient::class, SocialClient::class);
    }

    public function boot(): void
    {
        $this->configurePublishing();

        if ($this->app->runningUnitTests() && class_exists(SocialTestCommand::class)) {
            $this->commands([
                SocialTestCommand::class,
                NostrServe::class,
            ]);
        }
    }

    /**
     * Configure publishing for the package.
     */
    protected function configurePublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return; // @codeCoverageIgnore
        }

        $this->publishes([
            __DIR__.'/../../config/nostr.php' => $this->app->configPath('nostr.php'),
        ], 'nostr-config');
    }
}
