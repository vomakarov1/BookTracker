<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\ReadingEntry;

use BookTracker\Domain\Repository\ReadingEntryRepositoryInterface;

final class DeleteReadingEntryHandler
{
	public function __construct(
		private readonly ReadingEntryRepositoryInterface $readingEntryRepository,
	)
	{
	}

	public function handle(DeleteReadingEntryCommand $command): void
	{
		$this->readingEntryRepository->getById($command->id);
		$this->readingEntryRepository->delete($command->id);
	}
}
