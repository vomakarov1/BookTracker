<?php

declare(strict_types=1);

namespace BookTracker\Application\Query\Recommendation;

final readonly class GetRecommendationsQuery
{
	public function __construct(
		public string $userId,
		public int $limit = 5,
	)
	{
	}
}
