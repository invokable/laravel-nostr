<?php

namespace Revolution\Nostr\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Revolution\Nostr\Kind;
use WebSocket\BadOpcodeException;
use WebSocket\Client;
use WebSocket\ConnectionException;

class NostrServe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nostr:serve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected bool $running = true;

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws BadOpcodeException
     */
    public function handle(): int
    {
        if (! app()->runningUnitTests()) {
            $this->error('Testing only.');

            return 1;
        }

        $this->trap([SIGTERM, SIGINT], function ($signal) {
            $this->newLine();
            $this->info('signal: '.$signal);
            $this->running = false;
        });

        $sub_id = Str::random();

        try {
            $client = new Client(Arr::first(config('nostr.relays')));

            $client->send(json_encode([
                'REQ',
                $sub_id,
                [
                    'limit' => 2,
                    'kinds' => [Kind::Text],
                ],
            ]));
        } catch (ConnectionException $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        while ($this->running) {
            try {
                $response = $client->receive();
                $this->info($response);
                $this->line('----');
            } catch (ConnectionException $e) {
                $this->error($e->getMessage());
            }
        }

        try {
            $client->send(json_encode([
                'CLOSE',
                $sub_id,
            ]));

            $client->close();
        } catch (ConnectionException $e) {
            $this->error($e->getMessage());
        }

        return Command::SUCCESS;
    }
}
