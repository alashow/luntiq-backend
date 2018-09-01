<?php

namespace App\Model;

use Carbon\Carbon;
use App\Model\Traits\HasFile;
use Illuminate\Database\Eloquent\Model;
use App\Util\Downloader\DownloadableInterface;

class Movie extends Model implements DownloadableInterface
{
    use HasFile;

    protected $table = 'movies';

    /**
     * @param array     $movieResult movie object from search result
     * @param PremFile  $premFile    prem file
     * @param \stdClass $guessed     guessed instance
     *
     * @return Movie movie model instance created from given movie result and prem file
     */
    public static function build(array $movieResult, PremFile $premFile, \stdClass $guessed)
    {
        $movie = new Movie;

        $movie->file()->associate($premFile);
        $movie->tmdb_id = $movieResult['id'];
        $movie->title = $movieResult['title'];
        $movie->overview = $movieResult['overview'];
        $movie->vote_average = $movieResult['vote_average'];
        $movie->genres = join(',', $movieResult['genre_ids']);
        $movie->adult = $movieResult['adult'];
        $movie->poster_path = $movieResult['poster_path'];
        $movie->backdrop_path = $movieResult['backdrop_path'];
        $movie->release_date = Carbon::parse($movieResult['release_date']);
        $movie->download = config('luntiq.downloads.enable_for_new_media');

        if (isset($guessed->screen_size)) {
            $movie->quality = $guessed->screen_size;
        }

        return $movie;
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
     * @return PremFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @inheritdoc
     */
    public function buildFolderPath()
    {
        return sprintf('%s', config('luntiq.downloads.folders.movies'));
    }

    /**
     * @inheritdoc
     */
    public function buildFileName()
    {
        $title = $this->title;
        $year = substr($this->release_date, 0, 4);
        $fileExtension = pathinfo($this->file->name)['extension'];

        return sprintf('%s (%s).%s', $title, $year, $fileExtension);
    }

    /**
     * @inheritdoc
     */
    public function buildFullPath()
    {
        return sprintf('%s/%s', $this->buildFolderPath(), $this->buildFileName());
    }
}
