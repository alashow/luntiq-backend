<?php

namespace App\Http\Controllers\Api;

use App\Model\Show;
use App\Model\Movie;
use Illuminate\Http\JsonResponse;

class LibraryController extends BaseApiController
{
    /**
     * Just dump all movies and shows (only those with seasons and episodes).
     *
     * @return JsonResponse
     */
    public function index()
    {
        $movies = Movie::with('file')->latest()->get();

        $withSeasonsAndEpisodes = [
            'seasons' => function ($query) {
                $query->hasEpisodes();
            },
            'seasons.episodes.file',
        ];
        $shows = Show::hasSeasons()->with($withSeasonsAndEpisodes)->latest()->get();

        return $this->ok([
            'movies' => $movies,
            'shows'  => $shows,
        ]);
    }
}
