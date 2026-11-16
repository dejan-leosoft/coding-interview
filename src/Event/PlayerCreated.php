<?php

declare(strict_types=1);

namespace App\Event;

final readonly class PlayerCreated
{
    public function __construct(
        public int $playerId,
        public string $username,
        public string $email,
    ) {
    }
}
