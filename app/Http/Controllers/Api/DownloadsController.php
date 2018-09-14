<?php

namespace App\Http\Controllers\Api;

use App\Model\Show;
use App\Model\Season;
use App\Model\Episode;
use App\Model\PremFile;
use Illuminate\Http\Request;
use App\Util\DownloadManager;
use Illuminate\Database\Eloquent\Collection;

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
            return $this->notFound();
        }
    }

    /**
     * Get status stats of episodes on different levels.
     * Request params: all = all episodes, show = show id, season = season id
     * At least one of them is required and only one of them will be served at a time on level order.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function shows(Request $request)
    {
        if ($request->has('all')) {
            $episodes = Episode::with('file')->get();
            $episodeCount = Show::with('seasons')->get()->pluck('seasons')->flatten()->sum('episode_count');
        } else {
            if ($request->has('show')) {
                $show = Show::with(['episodes.file', 'seasons'])->find($request->get('show'));
                $episodes = $show->episodes;
                $episodeCount = $show->seasons->sum('episode_count');
            } else {
                if ($request->has('season')) {
                    $season = Season::with('episodes.file')->find($request->get('season'));
                    $episodes = $season->episodes;
                    $episodeCount = $season->episode_count;
                }
            }
        }

        if (isset($episodes) && isset($episodeCount)) {
            $status = $this->getStatus($episodes, $episodeCount);

            if (! is_null($status)) {
                return $this->ok(['status' => $status]);
            }
        }
        return $this->notFound();
    }

    /**
     * Generates status stats from given episodes.
     *
     * @param Collection $episodes
     * @param int        $episodeCount
     *
     * @return array status array
     */
    private function getStatus(Collection $episodes, int $episodeCount)
    {
        $statuses = $episodes->map(function ($episode) {
            return $episode->getStatus();
        });

        $unchecked = $episodes->filter(function ($episode) {
            return ! $episode->downloadable();
        });
        $dead = $statuses->filter(function ($status) {
            return $status['status'] == 'not_found';
        });
        $complete = $statuses->filter(function ($status) {
            return in_array($status['status'], ['exists', 'complete']);
        });
        $active = $statuses->filter(function ($status) {
            return $status['status'] == 'active';
        });
        $waiting = $statuses->filter(function ($status) {
            return $status['status'] == 'waiting';
        });

        $totalSize = $episodes->sum('file.size');
        $downloadedSize = $complete->sum('completedLength');
        $downloadingSize = $active->sum('completedLength');
        $speed = $active->sum('downloadSpeed');

        return [
            'episode_count' => $episodeCount,
            'scanned'       => count($episodes),
            'unchecked'     => count($unchecked),
            'dead'          => count($dead),
            'complete'      => count($complete),
            'active'        => count($active),
            'waiting'       => count($waiting),
            'size'          => [
                'total'       => $totalSize,
                'downloaded'  => $downloadedSize,
                'downloading' => $downloadingSize,
            ],

            'speed' => $speed,
        ];
    }
}
