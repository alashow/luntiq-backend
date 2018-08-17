<?php

namespace App\Http\Controllers\Api;

use App\Model\Movie;
use App\Model\Show;
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
        $movies = Movie::with('file')->get();
        $shows = Show::with('seasons.episodes.file')->get();

        return $this->ok([
            'movies' => $movies,
            'shows'  => $shows,
        ]);
    }
}
