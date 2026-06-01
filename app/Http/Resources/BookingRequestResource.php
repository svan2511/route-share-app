<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'load_id' => $this->load_id,
            'user_id' => $this->user_id,
            'owner_id' => $this->owner_id,
            'pickup_city' => $this->pickup_city,
            'drop_city' => $this->drop_city,
            'pickup_offset_minutes' => $this->pickup_offset_minutes,
            'status' => $this->status,
            'goods_description' => $this->goods_description,
            'load' => new LoadResource($this->whenLoaded('relatedLoad')),
            'user' => new UserResource($this->whenLoaded('user')),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'created_at' => $this->created_at,
        ];
    }
}
