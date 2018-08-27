<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SeasonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'overview' => $this->overview,
            'season'   => $this->season_number,
            'loading'  => false,
            'toggle'   => ! boolval($this->episodes->avg('download')),
            'episodes' => EpisodeResource::collection($this->episodes),
        ];
    }
}
