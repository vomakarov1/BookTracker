<?php

declare(strict_types=1);

namespace BookTracker\Application\DTO;

final readonly class RecommendationDTO
{
	public function __construct(
		public BookDTO $book,
		public float $score,
		public string $reason,
	)
	{
	}
}
