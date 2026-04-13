<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\ReadingEntry;

use BookTracker\Domain\Repository\ReadingEntryRepositoryInterface;

final class ChangeReadingStatusHandler
{
	public function __construct(
		private readonly ReadingEntryRepositoryInterface $readingEntryRepository,
	)
	{
	}

	public function handle(ChangeReadingStatusCommand $command): void
	{
		$entry = $this->readingEntryRepository->getById($command->readingEntryId);

		$entry->changeStatus($command->newStatus);

		$this->readingEntryRepository->save($entry);
	}
}
