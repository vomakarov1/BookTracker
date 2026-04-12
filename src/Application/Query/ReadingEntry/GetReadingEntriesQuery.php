<?php

declare(strict_types=1);

namespace BookTracker\Application\Query\ReadingEntry;

final readonly class GetReadingEntriesQuery
{
	public function __construct(
		public string $userId,
	)
	{
	}
}
