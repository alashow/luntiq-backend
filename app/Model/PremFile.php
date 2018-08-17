<?php

namespace App\Model;

use App\Util\GuessIt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PremFile extends Model
{
    protected $table = 'files';

    /**
     * @param \stdClass $file       pre file json object
     * @param string    $folderId   pre folder id
     * @param string    $folderName pre folder name
     *
     * @return array array of prem file fields parsed prem file.
     */
    public static function build(\stdClass $file, string $folderId = null, string $folderName = null)
    {
        $premFile = [];
        $premFile['prem_id'] = $file->id;
        $premFile['folder_id'] = $folderId;
        $premFile['folder'] = $folderName;
        $premFile['name'] = $file->name;
        $premFile['size'] = $file->size;
        $premFile['timestamp'] = Carbon::createFromTimestamp($file->created_at);
        $premFile['link'] = $file->link;
        $premFile['stream_link'] = $file->stream_link ? $file->stream_link : null;

        return $premFile;
    }

    /**
     * Scope to only video files.
     *
     * @param Builder $builder
     */
    public function scopeVideos(Builder $builder)
    {
        foreach (config('luntiq.files.videos') as $videoExtension) {
            $builder->orWhere('name', 'LIKE', "%.$videoExtension");
        }
    }

    /**
     * Scope to given file ids only.
     *
     * @param Builder $builder
     * @param array   $ids
     */
    public function scopeIds(Builder $builder, $ids)
    {
        $builder->whereIn('prem_id', $ids);
    }

    /**
     * Guesses file's metadata from it's name by using python lib https://guessit.readthedocs.io/en/latest/
     *
     * @return mixed
     */
    public function guessIt()
    {
        return GuessIt::guess($this);
    }
}
