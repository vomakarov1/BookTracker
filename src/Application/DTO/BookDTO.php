<?php

declare(strict_types=1);

namespace BookTracker\Application\DTO;

final readonly class BookDTO
{
	public function __construct(
		public string $id,
		public string $title,
		public string $author,
		public string $category,
		public int $complexity,
	)
	{
	}
}
