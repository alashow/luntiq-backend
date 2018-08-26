<?php

namespace App\Util;

use App\Model\PremFile;

class GuessIt
{
    /**
     * Guesses media file info from file's name.
     * Tries to execute 'guessit' command in shell. It must be installed first.
     * https://guessit.readthedocs.io/en/latest/
     *
     * @param PremFile $premFile
     *
     * @return mixed
     */
    public static function guess(PremFile $premFile)
    {
        $command = escapeshellarg(config('luntiq.scanner.guessit.path'));
        $itemName = escapeshellarg($premFile->name);
        return json_decode(shell_exec("$command -j {$itemName}"));
    }
}