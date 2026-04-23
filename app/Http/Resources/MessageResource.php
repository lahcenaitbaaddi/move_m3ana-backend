<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->user_id,
            'club_id'    => $this->club_id,
            'message'    => $this->message,
            'is_read'    => $this->is_read,
            'user'       => new UserResource($this->whenLoaded('user')),
            'club'       => new ClubResource($this->whenLoaded('club')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
