<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PremFileResource extends JsonResource
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
            'link'        => $this->link,
            'stream_link' => $this->stream_link,
        ];
    }
}
