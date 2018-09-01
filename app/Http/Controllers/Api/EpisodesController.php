<?php

namespace App\Http\Controllers\Api;

use App\Model\Episode;
use Illuminate\Http\Request;
use App\Http\Resources\EpisodeResource;
use App\Events\DownloadableCheckChangedEvent;

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
