<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'route' => new RouteResource($this->whenLoaded('route')),
            'from_city' => $this->from_city,
            'to_city' => $this->to_city,
            'vehicle_type' => $this->vehicle_type,
            'available_space' => $this->available_space,
            'departure_date' => $this->departure_date?->format('Y-m-d'),
            'departure_time' => $this->departure_time,
            'estimated_pickup_date' => $this->estimated_pickup_date ?? null,
            'estimated_pickup_time' => $this->estimated_pickup_time ?? null,
            'notes' => $this->notes,
            'phone' => $this->phone,
            'status' => $this->status,
            'expires_at' => $this->expires_at,
            'destination_stop_id' => $this->destination_stop_id,
            'created_at' => $this->created_at,
        ];
    }
}
