<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\ReadingEntry;

use BookTracker\Application\Exception\ValidationException;

final readonly class ChangeReadingStatusCommand
{
	public string $readingEntryId;
	public string $newStatus;

	public function __construct(string $readingEntryId, string $newStatus)
	{
		if (trim($readingEntryId) === '')
		{
			throw new ValidationException('Reading entry id must not be empty.');
		}

		if (trim($newStatus) === '')
		{
			throw new ValidationException('New status must not be empty.');
		}

		$this->readingEntryId = $readingEntryId;
		$this->newStatus = $newStatus;
	}
}
