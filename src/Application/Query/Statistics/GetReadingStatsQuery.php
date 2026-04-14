<?php

declare(strict_types=1);

namespace BookTracker\Application\Query\Statistics;

final readonly class GetReadingStatsQuery
{
	public function __construct(
		public string $userId,
	)
	{
	}
}
