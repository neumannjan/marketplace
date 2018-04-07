<?php

namespace App\Packages;

use App\Helpers\DevPackages;
use Composer\Script\Event;
use Illuminate\Foundation\Application;

class ComposerScripts
{
    public static function postAutoloadDump(Event $event)
    {
        $dev = $event->isDevMode() ? 'true' : 'false';

        $laravel = new Application(getcwd());
        $fileName = DevPackages::FILENAME;

        file_put_contents($laravel->bootstrapPath("cache/$fileName"), "<?php return $dev;");
    }
}