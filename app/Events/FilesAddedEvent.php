<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class FilesAddedEvent
{
    use SerializesModels;

    public $newFilesIds = [];

    /**
     * Create a new event instance.
     *
     * @param array $newFilesIds
     *
     * @return void
     */
    public function __construct(array $newFilesIds)
    {
        $this->newFilesIds = $newFilesIds;
    }
}
