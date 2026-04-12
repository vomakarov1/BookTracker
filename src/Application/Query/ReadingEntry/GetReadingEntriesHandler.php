<?php

declare(strict_types=1);

namespace BookTracker\Application\Query\ReadingEntry;

use BookTracker\Application\DTO\ReadingEntryDTO;
use BookTracker\Domain\Entity\ReadingEntry;
use BookTracker\Domain\Repository\ReadingEntryRepositoryInterface;

final class GetReadingEntriesHandler
{
	public function __construct(
		private readonly ReadingEntryRepositoryInterface $readingEntryRepository,
	)
	{
	}

	/** @return array<ReadingEntryDTO> */
	public function handle(GetReadingEntriesQuery $query): array
	{
		$entries = $this->readingEntryRepository->getByUserId($query->userId);

		return array_map(
			static fn(ReadingEntry $e) => new ReadingEntryDTO(
				id: $e->getId(),
				userId: $e->getUserId(),
				bookId: $e->getBookId(),
				status: $e->getStatus()->value,
				rating: $e->getRating()?->getValue(),
				startedAt: $e->getStartedAt()->format('Y-m-d H:i:s'),
				finishedAt: $e->getFinishedAt()?->format('Y-m-d H:i:s'),
			),
			$entries
		);
	}
}
