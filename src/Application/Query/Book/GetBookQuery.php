<?php

declare(strict_types=1);

namespace BookTracker\Application\Query\Book;

final readonly class GetBookQuery
{
	public function __construct(
		public string $id,
	)
	{
	}
}
