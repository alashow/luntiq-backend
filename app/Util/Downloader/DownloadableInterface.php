<?php

namespace App\Util\Downloader;

use App\Model\PremFile;

interface DownloadableInterface
{
    /**
     * Get Download file.
     *
     * @return PremFile
     */
    public function getFile();

    /**
     * Build folder path for this item.
     *
     * @return string
     */
    public function buildFolderPath();

    /**
     * Build file name for this item.
     *
     * @return string
     */
    public function buildFileName();

    /**
     * Build full path for this item.
     *
     * @return string
     */
    public function buildFullPath();
}