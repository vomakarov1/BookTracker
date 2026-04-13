<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\ReadingEntry;

use BookTracker\Application\Exception\ValidationException;
use BookTracker\Domain\Enum\ReadingStatus;
use ValueError;

final readonly class ChangeReadingStatusCommand
{
	public string $readingEntryId;
	public ReadingStatus $newStatus;

	public function __construct(string $readingEntryId, string $newStatus)
	{
		if (trim($readingEntryId) === '')
		{
			throw new ValidationException('Reading entry id must not be empty.');
		}

		try
		{
			$this->newStatus = ReadingStatus::from($newStatus);
		}
		catch (ValueError)
		{
			throw new ValidationException(
				sprintf(
					'Status must be one of: %s.',
					implode(', ', array_column(ReadingStatus::cases(), 'value')),
				),
			);
		}

		$this->readingEntryId = $readingEntryId;
	}
}
