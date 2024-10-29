<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BootQueue extends Command
{
    protected $signature = 'machine:start {id : the ID of the machine to be started.}';

    protected $description = "This command starts a Fly.io machine. It needs the machine's ID as input.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $flyApiHostname = "http://_api.internal:4280";
        $flyAuthToken = env("FLY_AUTH_TOKEN");
        $flyAppName = env("FLY_APP_NAME");

        $machineId = $this->argument('id');
        $response = Http::withHeaders([
            'Authorization' => "Bearer $flyAuthToken",
            'Content-Type' => 'application/json'
        ])->post("$flyApiHostname/v1/apps/$flyAppName/machines/$machineId/start");

        if ($response->failed())
        {
            $this->error($response);
            return Command::FAILURE;
        }

        $this->info($response);
        return Command::SUCCESS;
    }
}
