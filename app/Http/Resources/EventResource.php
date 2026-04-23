<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'club_id'     => $this->club_id,
            'title'       => $this->title,
            'description' => $this->description,
            'date'        => $this->date?->toISOString(),
            'location'    => $this->location,
            'capacity'    => $this->capacity,
            'image'       => $this->image ? asset('storage/' . $this->image) : null,
            'club'        => new ClubResource($this->whenLoaded('club')),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}
