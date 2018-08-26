<?php

namespace App\Http\Controllers\Api;

use App\Events\DownloadableCheckChangedEvent;
use App\Http\Resources\EpisodeResource;
use App\Model\Episode;
use App\Model\PremFile;
use App\Util\DownloadManager;
use Illuminate\Http\Request;

class DownloadsController extends BaseApiController
{

    /**
     * @var DownloadManager
     */
    protected $downloader;

    /**
     * DownloadsController constructor.
     *
     * @param DownloadManager $downloader
     */
    public function __construct(DownloadManager $downloader)
    {
        $this->downloader = $downloader;
    }

    public function check(PremFile $file)
    {
        $status = null;
        if ($file->movie()->exists()) {
            $status = $file->movie->getStatus();
        } else {
            if ($file->episode()->exists()) {
                $status = $file->episode->getStatus();
            }
        }

        if (! is_null($status)) {
            return $this->ok(['status' => $status]);
        } else {
            return $this->error("File doesn't have linked movie or episode", 400);
        }
    }
}
