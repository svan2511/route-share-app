<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'is_read' => $this->is_read,
            'load_id' => $this->load_id,
            'booking_id' => $this->booking_id,
            'from_user' => $this->whenLoaded('fromUser', fn () => [
                'id' => $this->fromUser->id,
                'name' => $this->fromUser->business_name ?? $this->fromUser->full_name,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
