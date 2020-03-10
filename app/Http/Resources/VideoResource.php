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
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'files' => [
                'video' => $this->video_file,
                'thumbnail' => $this->thumbnail_file,
                'banner_file' => $this->banner_file,
                'trailer_file' => $this->trailer_file,
            ]
        ];
    }
}
