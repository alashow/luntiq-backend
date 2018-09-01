<?php

namespace App\Console\Commands;

use App\Model\Movie;
use App\Model\Episode;
use App\Model\PremFile;
use App\Util\PremClient;
use App\Events\FilesAddedEvent;
use Illuminate\Console\Command;
use App\Util\Downloader\DownloadableInterface;

class SyncLibrary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:library';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans and builds library from files in Premiumize Cloud';

    protected $preClient;

    protected $scannedFiles = [];
    protected $scanFailIgnoreAfter;

    /**
     * Create a new command instance.
     *
     * @param PremClient $preClient
     */
    public function __construct(PremClient $preClient)
    {
        parent::__construct();
        $this->preClient = $preClient->getClient();
        $this->scanFailIgnoreAfter = config('luntiq.scanner.ignore_after');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->scanFolder();

        $files = PremFile::all(['prem_id', 'scanned', 'scan_fails']);
        $syncedFileIds = $files->pluck('prem_id')->toArray();

        $scannedFileIds = array_keys($this->scannedFiles);
        $addedFiles = array_diff($scannedFileIds, $syncedFileIds);
        $existingFiles = array_diff($scannedFileIds, $addedFiles);
        $removedFiles = array_diff($syncedFileIds, $scannedFileIds);

        // delete removed files
        if (config('luntiq.downloads.clean_removed')) {
            collect_merge(Movie::byFile($removedFiles)->get(), Episode::byFile($removedFiles)->get())->each(function (DownloadableInterface $hasFile) {
                $filePath = $hasFile->buildFullPath();
                $this->warn("Removing removed file located at (if exists): $filePath");
                @unlink($filePath);
            });
        }
        PremFile::ids($removedFiles)->delete();

        // add added files
        PremFile::insert(array_except($this->scannedFiles, $existingFiles));

        // do something about existing files
        $outdatedFiles = $this->diffAndUpdateExisting($existingFiles);

        $neglectedFiles = $files->filter(function ($file) {
            return ! $file->scanned && $file->scan_fails <= $this->scanFailIgnoreAfter;
        })->pluck('prem_id')->toArray();

        $this->warn("==================================================================================================================");

        $addedCount = count($addedFiles);
        $this->info("{$addedCount} files were added");

        $existingCount = count($existingFiles);
        $this->info("{$existingCount} files were existing");

        $outdatedCount = count($outdatedFiles);
        $this->info("{$outdatedCount} files were outdated.");

        $removedCount = count($removedFiles);
        $this->info("{$removedCount} files were removed");

        $neglectedCount = count($neglectedFiles);
        $this->info("{$neglectedCount} files were neglected / not scanned previously.");

        $this->warn("==================================================================================================================");

        $changes = $addedCount + $outdatedCount + $neglectedCount;
        if ($changes > 0) {
            $this->info("File changes detected, scanning new, updates, neglected files. Then updating the library. Watch the log file for details.");
            event(new FilesAddedEvent($addedFiles + $outdatedFiles + $neglectedFiles));
        } else {
            $this->info("No file changes detected. Only file links have been updated.");
        }

        $this->cleanDuplicateMedia();

        $this->call('sync:downloads');

        return 0;
    }

    /**
     * Scans folder recursively for files.
     *
     * @param string|null    $folderId
     * @param \stdClass|null $folder
     */
    private function scanFolder(string $folderId = null, \stdClass $folder = null)
    {
        if ($folder != null) {
            $this->info("Scanning folder: {$folder->name}");
        } else {
            $this->info("Scanning root folder");
        }

        $result = json_decode($this->preClient->get('folder/list', [
            'query' => ['id' => $folderId],
        ])->getBody());

        if ($result->status == 'success') {
            $items = $result->content;
            foreach ($items as $item) {
                switch ($item->type) {
                    case 'folder':
                        $this->scanFolder($item->id, $item);
                        break;
                    case 'file':
                        $this->scanFile($item, $folderId, $folder != null ? $folder->name : null);
                        break;
                    default:
                        $this->warn('Found unknown item type: '.$item);
                }
            }
        } else {
            $this->error('API Returned an error: '.$result->message);
        }
    }

    /**
     * Scans the file. If it's media, adds to scanned to files.
     *
     * @param \stdClass   $file
     * @param string|null $folderId
     * @param string|null $folderName
     */
    private function scanFile(\stdClass $file, string $folderId = null, string $folderName = null)
    {
        $this->info("Scanning file: {$file->name}");
        $supported = config('luntiq.files.supported');
        $pathInfo = pathinfo($file->name);

        if (in_array($pathInfo['extension'], $supported)) {
            $this->scannedFiles[$file->id] = PremFile::build($file, $folderId, $folderName);
        }
    }

    /**
     * Check diff of gathered existing files and synced files (in db). Update synced files if fields updated.
     *
     * @param $existingFileIds
     *
     * @return array
     */
    private function diffAndUpdateExisting($existingFileIds)
    {
        $equalityFields = ['name', 'folder_id', 'folder', 'size', 'link', 'stream_link'];
        $alwaysUpdatingFields = ['link', 'stream_link'];
        $equal = function ($existingFile, PremFile $premFile) use ($equalityFields, $alwaysUpdatingFields) {
            foreach ($equalityFields as $field) {
                if ($existingFile[$field] != $premFile->{$field}) {
                    // update the field on synced file
                    $premFile->{$field} = $existingFile[$field];
                    $isAlwaysUpdatingField = in_array($field, $alwaysUpdatingFields);
                    // reset scanned flag if other fields changed. Probably the name changed, which affects how the media guessing depends on.
                    if (! $isAlwaysUpdatingField) {
                        $premFile->resetScanned();
                    }
                    $premFile->save();

                    // hide that it's different if it's non-important field
                    if ($isAlwaysUpdatingField) {
                        continue;
                    } else {
                        $this->warn("{$premFile->name} file's '{$field}' was updated. Reset scanned flag for the file too.");
                        return false;
                    }
                }
            }
            return true;
        };

        $existingFiles = array_only($this->scannedFiles, $existingFileIds);
        $syncedFiles = PremFile::ids($existingFileIds)->get();

        $outdated = [];
        foreach ($existingFiles as $existingFileId => $existingFile) {
            $premFile = $syncedFiles->filter(function ($item) use ($existingFileId) {
                return $item->prem_id == $existingFileId;
            })->first();
            if (! $equal($existingFile, $premFile)) {
                $this->warn("{$premFile->name} file was updated.");
                $outdated[] = $existingFile;
            }
        }
        return $outdated;
    }

    /**
     * Clean up duplicates medias in database, file with same perm_id but different tmdb_id.
     */
    private function cleanDuplicateMedia()
    {
        $duplicates = function ($model) {
            $duplicates = ($model)::with('file')->orderBy('updated_at')->whereIn('prem_id', function ($query) use ($model) {
                $tableName = (new $model)->getTable();
                $query->select('prem_id')->from($tableName)->groupBy('prem_id')->havingRaw('count(*) > 1');
            })->get();

            if ($duplicates->isNotEmpty()) {
                $removal = $duplicates->count() - 1;
                ($model)::whereIn('id', $duplicates->take($removal)->pluck('id'))->delete();
                $this->warn("$removal $model duplicates were removed.");
            }
        };
        $duplicates(Episode::class);
        $duplicates(Movie::class);
    }
}

