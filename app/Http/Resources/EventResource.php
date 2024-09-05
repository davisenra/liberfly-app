<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'description' => $this->description,
            'date_time' => $this->date,
            'date' => $this->date->format('Y-m-d'),
            'time' => $this->date->format('H:i'),
            'venue' => new VenueResource($this->whenLoaded('venue')),
        ];
    }
}
