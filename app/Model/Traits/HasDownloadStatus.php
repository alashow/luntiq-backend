<?php

namespace App\Model\Traits;

trait HasDownloadStatus
{
    public function getStatus()
    {
        if ($this->file->download_id != null) {
            $statuses = resolve('Downloader')->check([$this]);
            if (! empty($statuses)) {
                return $statuses[0];
            }
        }

        return null;
    }
}