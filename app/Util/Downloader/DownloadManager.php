<?php

namespace App\Util;

use App\Util\Downloader\Aria2Client;
use App\Util\Downloader\DownloadableInterface;

class DownloadManager
{
    protected $aria2Client;

    /**
     * DownloadManager constructor.
     */
    public function __construct()
    {
        $this->aria2Client = new Aria2Client();
    }

    /**
     * @param DownloadableInterface[] $items
     *
     * @return array ids
     */
    private function getIds($items)
    {
        $ids = [];
        foreach ($items as $item) {
            $ids[] = $item->getFile()->download_id;
        }
        return $ids;
    }

    /**
     * @param DownloadableInterface[] $items
     *
     * @return array
     */
    public function start($items)
    {
        $files = [];
        foreach ($items as $item) {
            $files[] = [
                'key'    => $item->getFile()->prem_id,
                'folder' => $item->buildFolderPath(),
                'name'   => $item->buildFileName(),
                'url'    => $item->getFile()->link,
            ];

            // create empty file with item name if ghosting enabled
            if (config('luntiq.downloads.ghost.enabled')) {
                touch(sprintf("%s/%s", config('luntiq.downloads.ghost.path'), $item->buildFileName()));
            }
        }

        return $this->aria2Client->download($files);
    }

    /**
     * @param DownloadableInterface[] $items
     *
     * @return array
     */
    public function check($items)
    {
        return $this->aria2Client->checkStatus($this->getIds($items));
    }

    /**
     * @param string|array $ids download ids
     *
     * @return array
     */
    public function checkById($ids)
    {
        return $this->aria2Client->checkStatus(is_string($ids) ? [$ids] : $ids);
    }

    /**
     * @param DownloadableInterface[] $items
     *
     * @return array
     */
    public function cancel($items)
    {
        return $this->aria2Client->cancel($this->getIds($items));
    }
}
