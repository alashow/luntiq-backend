<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShowResource extends JsonResource
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
            'id'          => $this->id,
            'name'       => $this->name,
            'overview'    => $this->overview,
            'poster_path' => $this->poster_path,
            'download'    => boolval($this->download),
            'episodes'    => EpisodeResource::collection($this->seasons->pluck('episodes')->flatten()),
        ];
    }
}
