<?php

namespace App\Http\Controllers\Api;

use App\Model\Show;
use App\Model\Episode;
use Illuminate\Http\Request;
use App\Http\Resources\ShowResource;
use App\Events\DownloadableCheckChangedEvent;

class ShowsController extends BaseApiController
{
    public function shows()
    {
        $shows = Show::with('seasons.episodes.file')->hasSeasons()->latest()->get();

        return $this->ok([
            'shows' => ShowResource::collection($shows),
        ]);
    }

    public function clearAll()
    {
        Show::where('download', '=', true)->update([
            'download' => false,
        ]);

        Episode::where('download', '!=', null)->update([
            'download' => null,
        ]);

        event(new DownloadableCheckChangedEvent());

        return $this->ok();
    }

    public function show($show)
    {
        $show = Show::with('seasons.episodes.file')->find($show);

        return $this->ok(['show' => ShowResource::make($show)]);
    }

    public function toggleDownload(Request $request, Show $show)
    {
        $enable = $request->input('download', false);

        $show->download = $enable;
        $show->save();

        $recursive = $request->input('recursive', false);
        if ($recursive) {
            $episodeIds = $show->episodes->pluck('id');
            Episode::whereIn('id', $episodeIds)->where('download', '=', ! $enable)->update([
                'download' => $enable,
            ]);
        }

        event(new DownloadableCheckChangedEvent($show));

        return $this->ok([
            'enabled' => $enable,
        ]);
    }

    public function update(Request $request, $show)
    {
        $show = Show::with('seasons.episodes.file')->find($show);
        $enable = $request->input('download', false);

        $show->download = $enable;
        $show->save();

        return $this->ok(['show' => ShowResource::make($show)]);
    }

}
