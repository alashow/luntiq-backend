<?php

namespace App\Model\Traits;

use Illuminate\Database\Eloquent\Builder;

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
}