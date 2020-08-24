<?php

namespace App\Console\Commands;

use Log;
use App\Model\Movie;
use App\Model\Episode;
use App\Model\PremFile;
use App\Util\PremClient;
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

    /**
     * @var PremClient
     */
    protected $premClient;

    protected $movies;
    protected $episodes;
    protected $checkedMovies;
    protected $uncheckedMovies;
    protected $checkedEpisodes;
    protected $uncheckedEpisodes;

    /**
     * SyncDownloads constructor.
     *
     * @param DownloadManager $downloader
     * @param PremClient      $premClient
     */
    public function __construct(DownloadManager $downloader, PremClient $premClient)
    {
        parent::__construct();
        $this->premClient = $premClient->getClient();
        $this->downloader = $downloader;

        $this->checkedMovies = collect();
        $this->uncheckedMovies = collect();
        $this->checkedEpisodes = collect();
        $this->uncheckedEpisodes = collect();
    }

    /**
     *
     */
    public function handle()
    {
        if (! config('luntiq.downloads.enabled')) {
            exit('Downloads is not enabled. Check ENV DOWNLOADS_ENABLED');
        }

        $this->movies = Movie::with('file')->get();
        $this->episodes = Episode::with(['show', 'file'])->get();

        $this->movies->each(function ($movie) {
            if ($movie->download) {
                $this->checkedMovies->push($movie);
            } else $this->uncheckedMovies->push($movie);
        });

        $this->episodes->each(function (Episode $episode) {
            if ($episode->downloadable()) {
                $this->checkedEpisodes->push($episode);
            } else $this->uncheckedEpisodes->push($episode);
        });

        try {
            $this->disposeUnchecked();
            $this->startChecked();
            $this->cleanUpCompleted();
            $this->cleanUp();
        } catch (\Exception $exception) {
            $this->warn('Error while syncing: '.$exception->getMessage());
            Log::error('Error while syncing downloads', [$exception]);
        }
    }

    private function startChecked()
    {
        $items = collect_merge($this->checkedMovies, $this->checkedEpisodes);

        $newItems = $items->filter(function ($item) {
            return $item->file->download_id == null;
        });
        $newItemsCount = count($newItems);

        $statefulItems = $items->diff($newItems);
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
        if ($newItemsCount != $startedCount) {
            $this->warn("New items count and started count doesn't match. It's probably because downloader can't handle more items to queue up.");
        }
    }

    private function disposeUnchecked()
    {
        $items = collect_merge($this->uncheckedMovies, $this->uncheckedEpisodes);

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

    private function cleanUp()
    {
        $items = collect_merge($this->movies, $this->episodes)->filter(function ($item) {
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
                        $this->removeDownloadedMedia($item);
                        continue;
                    }
            }
        }

        if ($resync) {
            $this->handle();
        }
    }

    private function cleanUpCompleted()
    {
        if (config('luntiq.downloads.clean_completed')) {
            $this->info("Cleaning up completed downloads");

            $items = collect_merge($this->movies, $this->episodes)->filter(function ($item) {
                return $item->file->download_id != null;
            });

            $tasks = $this->downloader->check($items);

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
                    default:
                        if (@file_exists($item->buildFullPath())) {
                            $this->info("File '{$fileName}' has been downloaded, removing from premiumize");
                            $this->removeDownloadedMedia($item);
                            continue;
                        }
                }
            }
        }
    }

    private function removeDownloadedMedia($item)
    {
        $folderId = ($item instanceof Movie) ? $item->file->folder_id : null;
        $hasFolder = $folderId != null;
        $itemId = $folderId ?: $item->file->prem_id;
        $apiEndpoint = ($hasFolder ? 'folder/delete' : 'item/delete');

        $result = json_decode($this->premClient->get($apiEndpoint, [
            'query' => ['id' => $itemId],
        ])->getBody());

        if ($result->status == 'success') {
            $this->info("Media item '{$item->file->name}' was removed from premiumize.");
        }
    }
}

