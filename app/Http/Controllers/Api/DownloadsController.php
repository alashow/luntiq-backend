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

    public function check($id)
    {
        $status = null;

        $statuses = $this->downloader->checkById($id);
        if (! empty($statuses)) {
            $status = $statuses[0];
        }

        return $this->ok(['status' => $status]);
    }
}
