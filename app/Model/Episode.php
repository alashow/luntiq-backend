<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    protected $table = 'episodes';

    /**
     * @param array    $episodeResult episode object from season result
     * @param array    $seasonResult  season object from show result
     * @param PremFile $premFile
     *
     * @return Episode new build episode from given episode result, show result, prem file
     */
    public static function build(array $episodeResult, array $seasonResult, $premFile)
    {
        $episode = new Episode();

        $episode->file()->associate($premFile);
        $episode->tmdb_id = $episodeResult['id'];
        $episode->season_id = $seasonResult['id'];
        $episode->season_number = $episodeResult['season_number'];
        $episode->episode_number = $episodeResult['episode_number'];
        $episode->name = $episodeResult['name'];
        $episode->overview = $episodeResult['overview'];
        $episode->vote_average = $episodeResult['vote_average'];
        $episode->still_path = $episodeResult['still_path'];
        $episode->air_date = Carbon::parse($episodeResult['air_date']);

        return $episode;
    }

    /**
     * Prem file link.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo(PremFile::class, 'prem_id', 'prem_id');
    }

    /**
     * Safely Save the episode.
     * If no duplicates just save it. If there is duplicates, compare file sizes of and choose the one with largest file size (hoping it's the best quality).
     *
     * @return bool saved this one if true, otherwise didn't save and the other/existing one chosen.
     */
    public function safeSave()
    {
        $otherEpisode = Episode::with('file')->where('tmdb_id', '=', $this->tmdb_id)->first();
        if ($otherEpisode == null) {
            return $this->save();
        } else {
            if ($otherEpisode->file->size > $this->file->size) {
                return false;
            } else {
                $otherEpisode->forceDelete();
                return $this->save();
            }
        }
    }
}
