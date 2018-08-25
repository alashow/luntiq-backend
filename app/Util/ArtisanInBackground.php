<?php

namespace App\Util;

use Artisan;
use Log;
use Symfony\Component\Process\Process;

class ArtisanInBackground
{
    public static function run($command)
    {
        $artisanPath = env('ARTISAN_PATH', null);
        if ($artisanPath != null && @file_exists(sprintf('%s/artisan', $artisanPath))) {
            $process = new Process("php artisan $command > /dev/null 2>&1 &", $artisanPath);
            $process->start();
        } else {
            Log::warning("Couldn't run artisan command in background, artisan path not set or invalid. Running the command synchronously.", [
                $command, $artisanPath,
            ]);
            Artisan::call($command);
        }
    }
}