<?php

declare(strict_types=1);

namespace Revolution\Nostr\Providers;

use Revolution\Nostr\Client\Native\WebSocketHttpMixin;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\ServiceProvider;
use Revolution\Nostr\Console\SocialTestCommand;
use Revolution\Nostr\NostrManager;
use Revolution\Nostr\Social\SocialClient;

class NostrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/nostr.php',
            'nostr',
        );

        $this->app->scoped(NostrManager::class, NostrManager::class);
        $this->app->scoped(SocialClient::class, SocialClient::class);
    }

    public function boot(): void
    {
        $this->websocket();

        $this->configurePublishing();

        if ($this->app->runningUnitTests() && class_exists(SocialTestCommand::class)) {
            $this->commands([
                SocialTestCommand::class,
            ]);
        }
    }

    protected function websocket(): void
    {
        PendingRequest::mixin(new WebSocketHttpMixin());
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
