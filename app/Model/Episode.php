<?php

namespace App\Model;

use Carbon\Carbon;
use App\Model\Traits\HasFile;
use Illuminate\Database\Eloquent\Model;
use App\Util\Downloader\DownloadableInterface;

class Episode extends Model implements DownloadableInterface
{

    use HasFile;

    protected $table = 'episodes';

    /**
     * @param array    $episodeResult episode object from season result
     * @param array    $showResult    show object from search result
     * @param array    $seasonResult  season object from show result
     * @param PremFile $premFile
     *
     * @return Episode episode model instance created from given episode result, show result, prem file
     */
    public static function build(array $episodeResult, array $seasonResult, array $showResult, $premFile)
    {
        $episode = new Episode();

        $episode->file()->associate($premFile);
        $episode->tmdb_id = $episodeResult['id'];
        $episode->show_id = $showResult['id'];
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
     * Prem file.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo(PremFile::class, 'prem_id', 'prem_id');
    }

    /**
     * Show of this episode.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function show()
    {
        return $this->belongsTo(Show::class, 'show_id', 'tmdb_id');
    }

    /**
     * Season of this episode.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function season()
    {
        return $this->belongsTo(Season::class, 'season_id', 'tmdb_id');
    }

    /**
     * Is this episode checked for downloading.
     *
     * @return bool
     */
    public function downloadable()
    {
        switch ($this->download) {
            case null:
                return $this->show->download;
            default:
                return $this->download;
        }
    }

    /**
     * @return PremFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Build folder path for this episode.
     *
     * @return string
     */
    public function buildFolderPath()
    {
        $showName = $this->show->name;
        $seasonNumber = $this->season_number;

        return sprintf('%s/%s/Season %02d',
            config('luntiq.downloads.folders.shows'),
            $showName,
            $seasonNumber
        );
    }

    /**
     * Build file name for this episode.
     *
     * @return string
     */
    public function buildFileName()
    {
        $showName = $this->show->name;
        $seasonNumber = $this->season_number;
        $episodeNumber = $this->episode_number;
        $episodeName = $this->name;
        $fileExtension = pathinfo($this->file->name)['extension'];

        return sanitizeFileName(sprintf('%s - s%02de%02d - %s.%s',
            $showName, $seasonNumber, $episodeNumber, $episodeName, $fileExtension
        ));
    }

    /**
     * Build full path for this episode.
     *
     * @return string
     */
    public function buildFullPath()
    {
        return sprintf('%s/%s', $this->buildFolderPath(), $this->buildFileName());
    }
}
