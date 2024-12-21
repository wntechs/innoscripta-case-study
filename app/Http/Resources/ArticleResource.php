<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'author' => $this->author,
            'published_at' => $this->published_at,
            'source' => $this->aggregator,
            'external_url' => $this->external_url
        ];
    }
}
