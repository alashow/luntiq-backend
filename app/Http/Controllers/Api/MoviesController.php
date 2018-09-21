<?php

namespace App\Http\Controllers\Api;

use App\Model\Movie;
use Illuminate\Http\Request;
use App\Http\Resources\MovieResource;
use App\Events\DownloadableCheckChangedEvent;

class MoviesController extends BaseApiController
{
    public function movies()
    {
        $movies = Movie::latest()->get();

        return $this->ok([
            'movies' => MovieResource::collection($movies),
        ]);
    }

    public function show($id)
    {
        $movie = Movie::with('file')->find($id);
        if (is_null($movie)) {
            return $this->notFound();
        }

        return $this->ok(['movie' => MovieResource::make($movie)]);
    }

    public function toggleAll(Request $request)
    {
        $enable = $request->input('download', false);

        Movie::where('download', '=', ! $enable)->update([
            'download' => $enable,
        ]);

        event(new DownloadableCheckChangedEvent());

        return $this->ok([
            'enabled' => $enable,
        ]);
    }

    public function update(Request $request, Movie $movie)
    {
        $enable = $request->input('download', false);

        $movie->download = $enable;
        $movie->save();

        event(new DownloadableCheckChangedEvent($movie));

        return $this->ok(['movie' => MovieResource::make($movie)]);
    }
}
