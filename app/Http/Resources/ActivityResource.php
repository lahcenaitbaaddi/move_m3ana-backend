<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'club_id'     => $this->club_id,
            'title'       => $this->title,
            'description' => $this->description,
            'sport'       => $this->sport,
            'location'    => $this->location,
            'city'        => $this->city,
            'date'        => $this->date?->toISOString(),
            'capacity'    => $this->capacity,
            'image'       => $this->image ? asset('storage/' . $this->image) : null,
            'is_active'   => $this->is_active,
            'is_promoted' => $this->whenLoaded('promotions', fn() => $this->isPromoted()),
            'club'        => new ClubResource($this->whenLoaded('club')),
            'user'        => new UserResource($this->whenLoaded('user')),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}
