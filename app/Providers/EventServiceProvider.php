<?php

namespace App\Providers;

use App\Events\DownloadableCheckChangedEvent;
use App\Events\FilesAddedEvent;
use App\Listeners\DownloadableCheckChangeListener;
use App\Listeners\NewFilesListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        FilesAddedEvent::class => [NewFilesListener::class],

        DownloadableCheckChangedEvent::class => [DownloadableCheckChangeListener::class],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
