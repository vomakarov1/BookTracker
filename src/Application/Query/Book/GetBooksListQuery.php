<?php

declare(strict_types=1);

namespace BookTracker\Application\Query\Book;

final readonly class GetBooksListQuery
{
	public function __construct(
		public ?string $category = null,
		public ?string $author = null,
	)
	{
	}
}
