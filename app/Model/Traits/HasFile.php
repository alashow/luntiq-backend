<?php

namespace App\Model\Traits;

use Illuminate\Database\Eloquent\Builder;
use Log;

trait HasFile
{
    public function getStatus()
    {
        $checkFile = function () {
            $file = $this->buildFullPath();
            if (@file_exists($file)) {
                return [
                    'status'          => 'exists',
                    'totalLength'     => $this->file->size,
                    'completedLength' => filesize($file),
                    'downloadSpeed'   => 0,
                ];
            } else {
                return [
                    'status'          => 'not_found',
                    'totalLength'     => 0,
                    'completedLength' => 0,
                    'downloadSpeed'   => 0,
                ];
            }
        };

        if ($this->file->download_id != null) {
            $statuses = resolve('Downloader')->check([$this]);
            if (! empty($statuses)) {
                return $statuses[0];
            } else {
                return $checkFile();
            }
        } else {
            return $checkFile();
        }
    }

    public function scopeByFile(Builder $builder, array $premIds)
    {
        $builder->with(['file'])->whereIn('prem_id', $premIds);
    }

    /**
     * Safely Save the movie / episode.
     * If no duplicates it just saves it. If there is duplicates, compares file sizes and chooses the one with largest file size (hoping it's the better quality).
     *
     * @return bool saved this one if true, otherwise didn't save and the other/existing one chosen (or saving failed).
     */
    public function safeSave()
    {
        $other = self::with('file')->where('tmdb_id', '=', $this->tmdb_id)->first();
        if ($other == null) {
            return $this->save();
        } else {
            if ($other->file->size > $this->file->size) {
                Log::warning("Skipping a movie because older version has better quality.", [
                    $other, $this,
                ]);
                return false;
            } else {
                Log::warning("Deleting old version of the movie and saving better quality.", [
                    $other, $this,
                ]);

                @unlink($other->buildFullPath());
                $other->forceDelete();

                return $this->save();
            }
        }
    }
}