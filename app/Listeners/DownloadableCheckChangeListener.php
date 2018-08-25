<?php

namespace App\Listeners;

use App\Events\DownloadableCheckChangedEvent;
use App\Util\ArtisanInBackground;
use Symfony\Component\Process\Process;

class DownloadableCheckChangeListener
{

    /**
     * Handle the event.
     *
     * @param  DownloadableCheckChangedEvent $event
     *
     * @return void
     */
    public function handle($event)
    {
        ArtisanInBackground::run('sync:downloads');
    }
}
