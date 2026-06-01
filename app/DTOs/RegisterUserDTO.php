<?php

namespace App\DTOs;

class RegisterUserDTO
{
    public function __construct(
        public readonly string $full_name,
        public readonly string $business_name,
        public readonly string $phone,
        public readonly string $city,
        public readonly string $market_type,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            full_name: $data['full_name'],
            business_name: $data['business_name'],
            phone: $data['phone'],
            city: $data['city'],
            market_type: $data['market_type'],
        );
    }
}
