<?php

namespace App\Http\Controllers\Api;

use App\Events\DownloadableCheckChangedEvent;
use App\Http\Resources\SeasonResource;
use App\Model\Episode;
use App\Model\Season;
use App\Model\Show;
use Illuminate\Http\Request;
use App\Http\Resources\ShowResource;

class SeasonsController extends BaseApiController
{

    public function season($id)
    {
        $show = Season::with('episodes.file')->find($id);

        return $this->ok(['season' => SeasonResource::make($show)]);
    }

    public function toggleDownload(Request $request, Season $season)
    {
        $enable = $request->input('download', false);

        $episodeIds = $season->episodes->pluck('id');
        Episode::whereIn('id', $episodeIds)->where('download', '=', ! $enable)->update([
            'download' => $enable,
        ]);

        event(new DownloadableCheckChangedEvent($season));

        return $this->ok([
            'enabled' => $enable,
        ]);
    }
}
