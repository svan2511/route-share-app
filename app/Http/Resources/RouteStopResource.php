<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteStopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stop_name' => $this->stop_name,
            'stop_order' => $this->stop_order,
            'time_offset_minutes' => $this->time_offset_minutes,
        ];
    }
}
