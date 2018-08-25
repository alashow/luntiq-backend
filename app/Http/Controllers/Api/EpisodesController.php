<?php

namespace App\Http\Controllers\Api;

use App\Events\DownloadableCheckChangedEvent;
use App\Http\Resources\EpisodeResource;
use App\Model\Episode;
use Illuminate\Http\Request;

class EpisodesController extends BaseApiController
{

    public function update(Request $request, Episode $episode)
    {
        $enable = $request->input('download', false);

        $episode->download = $enable;
        $episode->save();

        event(new DownloadableCheckChangedEvent($episode));

        return $this->ok(['episode' => EpisodeResource::make($episode)]);
    }
}
