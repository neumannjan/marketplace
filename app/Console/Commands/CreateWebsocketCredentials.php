<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Str;

class CreateWebsocketCredentials extends Command
{
    use ConfirmableTrait;

    const ENV_APP_ID = 'WEBSOCKET_APP_ID';
    const ENV_KEY = 'WEBSOCKET_KEY';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate websocket client API keys';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $currentAppID = env(self::ENV_APP_ID);
        $currentKey = env(self::ENV_KEY);

        if (strlen($currentKey) !== 0 && strlen($currentAppID) !== 0 && !$this->confirmToProceed()) {
            return false;
        }

        $envFilePath = $this->laravel->environmentFilePath();

        $envFile = file_get_contents($envFilePath);

        $envFile = $this->setKey(self::ENV_APP_ID, Str::random(16), $envFile, $currentAppID);
        $envFile = $this->setKey(self::ENV_KEY, Str::random(32), $envFile, $currentKey);

        file_put_contents($envFilePath, $envFile);

        return true;
    }

    protected function setKey($key, $value, $subject, $oldValue = '')
    {
        $escaped = preg_quote("=$oldValue", '/');

        return preg_replace(
            "/^{$key}{$escaped}/m",
            "$key=$value",
            $subject
        );
    }
}
