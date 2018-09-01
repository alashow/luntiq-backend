<?php

namespace App\Listeners;

use App\Util\ArtisanInBackground;
use App\Events\DownloadableCheckChangedEvent;

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
