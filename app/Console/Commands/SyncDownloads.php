<?php

namespace App\Console\Commands;

use Log;
use App\Model\Movie;
use App\Model\Episode;
use App\Model\PremFile;
use App\Util\DownloadManager;
use Illuminate\Console\Command;

class SyncDownloads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:downloads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads files checked for downloading in library from Premiumize Cloud';

    /**
     * @var DownloadManager
     */
    protected $downloader;

    protected $movies = [];
    protected $episodes = [];
    protected $checkedMovies = [];
    protected $uncheckedMovies = [];
    protected $checkedEpisodes = [];
    protected $uncheckedEpisodes = [];

    /**
     * SyncDownloads constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->downloader = new DownloadManager();
    }

    /**
     *
     */
    public function handle()
    {
        if (! env('DOWNLOADS_ENABLED', false)) {
            exit('Downloads is not enabled. Check ENV DOWNLOADS_ENABLED');
        }

        $this->movies = Movie::with('file')->get();
        $this->episodes = Episode::with(['show', 'file'])->get();

        $this->movies->each(function ($movie) {
            if ($movie->download) {
                $this->checkedMovies[] = $movie;
            } else $this->uncheckedMovies[] = $movie;
        });

        $this->episodes->each(function (Episode $episode) {
            if ($episode->downloadable()) {
                $this->checkedEpisodes[] = $episode;
            } else $this->uncheckedEpisodes[] = $episode;
        });

        try {
            $this->disposeUnchecked();
            $this->startChecked();
            $this->cleanUpFailed();
        } catch (\Exception $exception) {
            $this->warn('Error while syncing: '.$exception->getMessage());
            Log::error('Error while syncing downloads', [$exception]);
        }
    }

    private function startChecked()
    {
        $items = $this->checkedMovies + $this->checkedEpisodes;
        $newItems = array_filter($items, function ($item) {
            return $item->file->download_id == null;
        });

        $statefulItems = array_diff($items, $newItems);
        $statefulItemsCount = count($statefulItems);

        if (! empty($newItems)) {
            $started = $this->downloader->start($newItems);
            $startedCount = count($started);

            foreach ($started as $id => $downloadId) {
                PremFile::id($id)->update([
                    'download_id' => $downloadId,
                ]);
            }
        } else {
            $startedCount = 0;
        }

        $this->info("Started downloading {$startedCount} items. {$statefulItemsCount} items were already downloading or finished.");

    }

    private function disposeUnchecked()
    {
        $items = $this->uncheckedMovies + $this->uncheckedEpisodes;

        if (! empty($items)) {
            $this->downloader->cancel($items);
        }

        $removedItems = [];
        foreach ($items as $item) {
            $file = $item->buildFullPath();

            if (@file_exists($file)) {
                $this->warn("Removing unchecked item from storage: $file");
                @unlink($file);
                $removedItems[] = $item;
            }
        }

        PremFile::ids(collect($items)->pluck('file')->pluck('prem_id')->toArray())->update([
            'download_id' => null,
        ]);
    }

    private function cleanUpFailed()
    {
        $items = $this->checkedMovies + $this->uncheckedMovies + $this->checkedEpisodes + $this->uncheckedEpisodes;
        $items = array_filter($items, function ($item) {
            return $item->file->download_id != null;
        });

        $tasks = $this->downloader->check($items);

        foreach ($tasks as $task) {
            if (isset($task->status)) {
                switch ($task->status) {
                    case 'error':
                        // reset download id if download task status is error
                        PremFile::downloadId($task->gid)->update([
                            'download_id' => null,
                        ]);
                        break;
                }
            }
        }

        $resync = false;
        foreach ($items as $item) {
            $task = array_first(array_filter($tasks, function ($task) use ($item) {
                return $task->gid == $item->file->download_id;
            }));

            $status = $task ? $task->status : null;
            $fileName = $item->buildFileName();
            switch ($status) {
                case 'active':
                case 'waiting':
                case 'removed':
                    $this->info("File '{$fileName}' has status: '{$status}'");
                    continue;
                default:
                    $this->info("File '{$fileName}' has complete or unknown status: '{$status}', checking existence.");
                    if (! @file_exists($item->buildFullPath())) {
                        $this->warn("File '{$fileName}' was not found in the storage, resetting downloadId.");
                        $item->file->download_id = null;
                        $item->file->save();
                        $resync = true;
                    } else {
                        $this->info("File '{$fileName}' was found in the storage.");
                        continue;
                    }
            }
        }

        if ($resync) {
            $this->handle();
        }
    }
}

