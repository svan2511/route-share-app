<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'business_name' => $this->business_name,
            'business_logo' => $this->business_logo_url,
            'phone' => $this->phone,
            'city' => $this->city,
            'address' => $this->address,
            'market_type' => $this->market_type,
            'loads_count' => $this->whenHas('loads_count'),
            'created_at' => $this->created_at,
        ];
    }
}
