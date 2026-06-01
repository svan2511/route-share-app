<?php

namespace App\DTOs;

class StoreLoadDTO
{
    public function __construct(
        public readonly string $from_city,
        public readonly string $to_city,
        public readonly string $vehicle_type,
        public readonly int $available_space,
        public readonly string $departure_date,
        public readonly string $departure_time,
        public readonly ?string $notes,
        public readonly string $phone,
        public readonly ?int $route_id = null,
        public readonly ?int $destination_stop_id = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            from_city: $data['from_city'],
            to_city: $data['to_city'],
            vehicle_type: $data['vehicle_type'],
            available_space: (int) $data['available_space'],
            departure_date: $data['departure_date'],
            departure_time: $data['departure_time'],
            notes: $data['notes'] ?? null,
            phone: $data['phone'],
            route_id: isset($data['route_id']) ? (int) $data['route_id'] : null,
            destination_stop_id: isset($data['destination_stop_id']) ? (int) $data['destination_stop_id'] : null,
        );
    }
}
