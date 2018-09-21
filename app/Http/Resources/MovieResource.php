<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
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
            'title'       => $this->title,
            'overview'    => $this->overview,
            'poster_path' => $this->poster_path,
            'backdrop_path' => $this->backdrop_path,
            'download'    => boolval($this->download),
            'file'        => PremFileResource::make($this->file),
        ];
    }
}
