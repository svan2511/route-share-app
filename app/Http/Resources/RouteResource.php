<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'route_name' => $this->route_name,
            'from_city' => $this->from_city,
            'to_city' => $this->to_city,
            'destination_offset_minutes' => $this->destination_offset_minutes,
            'stops' => RouteStopResource::collection($this->whenLoaded('stops')),
        ];
    }
}
