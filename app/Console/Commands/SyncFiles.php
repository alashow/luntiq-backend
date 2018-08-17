<?php

namespace App\Console\Commands;

use App\Events\FilesAddedEvent;
use App\Model\Episode;
use App\Model\Movie;
use App\Model\PremFile;
use App\Util\PremClient;
use Illuminate\Console\Command;

class SyncFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs files from Premiumize';

    protected $preClient;

    protected $scannedFiles = [];

    /**
     * Create a new command instance.
     *
     * @param PremClient $preClient
     */
    public function __construct(PremClient $preClient)
    {
        parent::__construct();
        $this->preClient = $preClient->getClient();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->scanFolder();

        if (! empty($this->scannedFiles)) {
            $syncedFileIds = PremFile::all(['prem_id'])->pluck('prem_id')->toArray();
            $scannedFileIds = array_keys($this->scannedFiles);
            $addedFiles = array_diff($scannedFileIds, $syncedFileIds);
            $existingFiles = array_diff($scannedFileIds, $addedFiles);
            $removedFiles = array_diff($syncedFileIds, $scannedFileIds);

            // delete removed files
            PremFile::ids($removedFiles)->delete();

            // add added files
            PremFile::insert(array_except($this->scannedFiles, $existingFiles));

            // do something about existing files
            $outdatedFiles = $this->diffAndUpdateExisting($existingFiles);

            if (! empty($addedFiles) || ! empty($outdatedFiles)) {
                event(new FilesAddedEvent($addedFiles + $outdatedFiles));
            }

            $this->info("==================================================================================================================");

            $addedCount = count($addedFiles);
            $this->info("{$addedCount} files were added");

            $existingCount = count($existingFiles);
            $this->info("{$existingCount} files were existing");

            $outdatedCount = count($outdatedFiles);
            $this->info("{$outdatedCount} files were outdated, updated.");

            $removedCount = count($removedFiles);
            $this->info("{$removedCount} files were removed");
        }

        $this->cleanDuplicateMedia();
        return 0;
    }

    /**
     * Scan's folder recursively for files.
     *
     * @param string|null    $folderId
     * @param \stdClass|null $folder
     *
     * @return int
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
     * Scan's the file. If it's media, adds to scanned to files.
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
        $equal = function ($existingFile, $premFile) use ($equalityFields, $alwaysUpdatingFields) {
            foreach ($equalityFields as $field) {
                if ($existingFile[$field] != $premFile->{$field}) {
                    // update the field on synced file
                    $premFile->{$field} = $existingFile[$field];
                    $premFile->save();
                    // hide that it's different if it's non-important field
                    if (in_array($field, $alwaysUpdatingFields)) {
                        continue;
                    } else {
                        $this->warn("{$premFile->name} file's '{$field}' was updated.");
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

