<?php

namespace App\Events;

use App\Util\Downloader\DownloadableInterface;
use Illuminate\Foundation\Events\Dispatchable;

class DownloadableCheckChangedEvent
{
    use Dispatchable;

    public $items;

    /**
     * Create a new event instance.
     *
     * @param DownloadableInterface[] $items
     */
    public function __construct(...$items)
    {
        $this->items = $items;
    }
}
