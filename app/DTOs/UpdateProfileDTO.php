<?php

namespace App\DTOs;

class UpdateProfileDTO
{
    public function __construct(
        public readonly ?string $full_name,
        public readonly ?string $business_name,
        public readonly ?string $city,
        public readonly ?string $phone,
        public readonly ?string $market_type,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            full_name: $data['full_name'] ?? null,
            business_name: $data['business_name'] ?? null,
            city: $data['city'] ?? null,
            phone: $data['phone'] ?? null,
            market_type: $data['market_type'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'full_name' => $this->full_name,
            'business_name' => $this->business_name,
            'city' => $this->city,
            'phone' => $this->phone,
            'market_type' => $this->market_type,
        ], fn ($value) => $value !== null);
    }
}
