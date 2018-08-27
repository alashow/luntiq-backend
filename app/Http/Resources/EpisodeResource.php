<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeResource extends JsonResource
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
            'name'        => $this->name,
            'overview'    => $this->overview,
            'episode'     => $this->episode_number,
            'poster_path' => $this->poster_path,
            'download'    => is_null($this->download) ? null : boolval($this->download),
            'file'        => PremFileResource::make($this->file),
        ];
    }
}
