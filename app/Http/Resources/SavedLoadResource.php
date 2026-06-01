<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavedLoadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'load' => new LoadResource($this->whenLoaded('savedLoad')),
            'saved_at' => $this->created_at,
        ];
    }
}
