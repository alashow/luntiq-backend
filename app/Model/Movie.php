<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $table = 'movies';

    /**
     * @param array     $movieResult movie object from search result
     * @param PremFile  $premFile prem file
     * @param \stdClass $guessed guessed instance
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
}
