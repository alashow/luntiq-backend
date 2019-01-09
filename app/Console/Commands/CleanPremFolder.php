<?php

namespace App\Console\Commands;

use App\Util\PremClient;
use Illuminate\Console\Command;

class CleanPremFolder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prem:clean
                            {pattern : Pattern to clean}
                            {replace : Text to replace with}
                            {--folder= : The ID of the folder. Defaults to root folder}
                            {--regex : Is pattern regex. False by default}
                            {--force : Don\'t ask before cleaning each file}
                            {--dry-run : Dry run. Don\'t actually send rename requests}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans file names by applying given regex in Premiumize Cloud folder';

    protected $preClient;
    protected $pattern;
    protected $patternRegex;
    protected $replace;
    protected $targetFolder;
    protected $force;
    protected $isRegex;
    protected $dryRun;

    protected $cleanedCount = 0;

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
        $this->pattern = $this->argument('pattern');
        $this->replace = $this->argument('replace');

        $this->patternRegex = sprintf('~%s~', $this->pattern);
        $this->targetFolder = $this->option('folder');
        $this->force = $this->option('force');
        $this->isRegex = $this->option('regex');
        $this->dryRun = $this->option('dry-run');

        $this->cleanFolder($this->targetFolder);
        $this->warn("-----------------------------------");
        $this->warn("Cleaned $this->cleanedCount files.");

        return 0;
    }

    /**
     * Scans folder recursively for files.
     *
     * @param string|null    $folderId
     * @param \stdClass|null $folder
     */
    private function cleanFolder(string $folderId = null, \stdClass $folder = null)
    {
        if ($folder != null) {
            $this->info("Cleaning folder: {$folder->name}");
        } else {
            $this->info("Cleaning root folder");
        }

        $result = json_decode($this->preClient->get('folder/list', [
            'query' => ['id' => $folderId],
        ])->getBody());

        if ($result->status == 'success') {
            $items = $result->content;
            foreach ($items as $item) {
                switch ($item->type) {
                    case 'folder':
                        $this->cleanFolder($item->id, $item);
                        break;
                    case 'file':
                        $this->cleanFile($item);
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
     * Clean the file if it needs cleaning.
     *
     * @param \stdClass $file
     */
    private function cleanFile(\stdClass $file)
    {
        $this->info("Cleaning file: {$file->name}");
        $id = $file->id;
        $name = $file->name;

        if ($this->needsCleaning($name)) {
            $cleanName = $this->cleanName($name);
            if ($this->force || $this->confirm("Do you really want to rename '$name' to '$cleanName'")) {
                if ($this->dryRun) {
                    $this->warn("DRY cleaned file: {$file->name} -> $cleanName");
                    $this->cleanedCount++;
                } else {
                    $result = json_decode($this->preClient->post('item/rename', [
                        'form_params' => ['id' => $id, 'name' => $cleanName],
                    ])->getBody());

                    if ($result->status == 'success') {
                        $this->warn("Cleaned file: {$file->name} -> $cleanName");
                        $this->cleanedCount++;
                    } else {
                        $this->error("Couldn't clean file: {$file->name} -> $cleanName");
                    }
                }
            } else {
                $this->warn("Didn't clean '$name'.");
            }
        }
    }

    /**
     * Check if the name needs cleaning.
     *
     * @param $name
     *
     * @return bool
     */
    private function needsCleaning($name)
    {
        if ($this->isRegex) {
            return preg_match($this->patternRegex, $name) == 1;
        } else {
            return str_contains($name, $this->pattern);
        }
    }

    /**
     * Clean the name.
     *
     * @param $name
     *
     * @return string
     */
    private function cleanName($name)
    {
        if ($this->isRegex) {
            return preg_replace($this->patternRegex, $this->replace, $name);
        } else {
            return str_replace($this->pattern, $this->replace, $name);
        }
    }
}

