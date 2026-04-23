<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'user_id'           => $this->user_id,
            'name'              => $this->name,
            'sport'             => $this->sport,
            'description'       => $this->description,
            'address'           => $this->address,
            'city'              => $this->city,
            'phone'             => $this->phone,
            'email'             => $this->email,
            'tarifs'            => $this->tarifs,
            'horaires'          => $this->horaires,
            'logo'              => $this->logo ? asset('storage/' . $this->logo) : null,
            'images'            => $this->images,
            'social_links'      => $this->social_links,
            'latitude'          => $this->latitude,
            'longitude'         => $this->longitude,
            'is_approved'       => $this->is_approved,
            'is_active'         => $this->is_active,
            'average_rating'    => round($this->average_rating, 1),
            'subscribers_count' => $this->subscribers_count,
            'owner'             => new UserResource($this->whenLoaded('user')),
            'created_at'        => $this->created_at?->toISOString(),
            'updated_at'        => $this->updated_at?->toISOString(),
        ];
    }
}
