<?php

declare(strict_types=1);

namespace BookTracker\Application\DTO;

final readonly class UserDTO
{
	public function __construct(
		public string $id,
		public string $name,
		public string $email,
	)
	{
	}
}
