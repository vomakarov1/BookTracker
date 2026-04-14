<?php

declare(strict_types=1);

namespace BookTracker\Application\DTO;

use BookTracker\Domain\Enum\ReadingStatus;

final readonly class ReadingEntryDTO
{
	public function __construct(
		public string $id,
		public string $userId,
		public string $bookId,
		public ReadingStatus $status,
		public ?int $rating,
		public string $startedAt,
		public ?string $finishedAt,
	)
	{
	}
}
