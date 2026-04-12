<?php

declare(strict_types=1);

namespace BookTracker\Domain\Service;

use BookTracker\Domain\Entity\Book;

final readonly class RecommendationResult
{
	public function __construct(
		public Book $book,
		public float $score,
		public string $reason,
	)
	{
	}
}
