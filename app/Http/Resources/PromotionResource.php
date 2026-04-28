<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'user_id'         => $this->user_id,
            'promotable_type' => class_basename($this->promotable_type),
            'promotable_id'   => $this->promotable_id,
            'promotable'      => $this->whenLoaded('promotable'),
            'start_date'      => $this->start_date?->toISOString(),
            'end_date'        => $this->end_date?->toISOString(),
            'status'          => $this->status,
            'is_boosted'      => $this->is_boosted,
            'is_active'       => $this->isActive(),
            'payment_id'      => $this->payment_id,
            'payment_status'  => $this->payment_status,
            'user'            => new UserResource($this->whenLoaded('user')),
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),
        ];
    }
}
