<?php

namespace App\DTOs;

class LoginDTO
{
    public function __construct(
        public readonly string $phone,
        public readonly string $password,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            phone: $data['phone'],
            password: $data['password'],
        );
    }
}
