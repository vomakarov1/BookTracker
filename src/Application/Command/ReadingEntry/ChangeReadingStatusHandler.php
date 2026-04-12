<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\ReadingEntry;

use BookTracker\Application\Exception\ValidationException;
use BookTracker\Domain\Enum\ReadingStatus;
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

		$status = ReadingStatus::tryFrom($command->newStatus);

		if ($status === null)
		{
			throw new ValidationException(
				sprintf('Invalid reading status "%s".', $command->newStatus)
			);
		}

		$entry->changeStatus($status);

		$this->readingEntryRepository->save($entry);
	}
}
