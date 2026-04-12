<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\ReadingEntry;

use BookTracker\Domain\Repository\ReadingEntryRepositoryInterface;
use BookTracker\Domain\ValueObject\ReadingEntryRating;

final class RateReadingEntryHandler
{
	public function __construct(
		private readonly ReadingEntryRepositoryInterface $readingEntryRepository,
	)
	{
	}

	public function handle(RateReadingEntryCommand $command): void
	{
		$entry = $this->readingEntryRepository->getById($command->readingEntryId);

		$rating = new ReadingEntryRating($command->rating);

		$entry->rate($rating);

		$this->readingEntryRepository->save($entry);
	}
}
