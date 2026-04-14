<?php

declare(strict_types=1);

namespace BookTracker\Application\DTO;

final readonly class ReadingStatsDTO
{
	public function __construct(
		/** @var array<string, int> */
		public array $countsByStatus,
		/** @var array<string, float> */
		public array $averageRatingByAuthor,
		/** @var array<string, int> */
		public array $finishedByMonth,
	)
	{
	}
}
