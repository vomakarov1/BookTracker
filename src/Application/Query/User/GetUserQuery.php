<?php

declare(strict_types=1);

namespace BookTracker\Application\Query\User;

final readonly class GetUserQuery
{
	public function __construct(
		public string $id,
	)
	{
	}
}
