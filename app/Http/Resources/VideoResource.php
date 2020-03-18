<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'classification' => $this->classification,
            'release_at' => $this->release_at,
            'duration' => $this->duration,
            'categories' => CategoryResource::collection($this->categories),
            'genres' => GenreResource::collection($this->genres),
            'cast_members' => CastMemberResource::collection($this->castMembers),
            'video' => $this->video_url,
            'thumbnail' => $this->thumbnail_url,
            'banner' => $this->banner_url,
            'trailer' => $this->trailer_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
