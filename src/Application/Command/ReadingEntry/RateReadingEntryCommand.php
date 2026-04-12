<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\ReadingEntry;

use BookTracker\Application\Exception\ValidationException;

final readonly class RateReadingEntryCommand
{
	public string $readingEntryId;
	public int $rating;

	public function __construct(string $readingEntryId, int $rating)
	{
		if (trim($readingEntryId) === '')
		{
			throw new ValidationException('Reading entry id must not be empty.');
		}

		if ($rating < 1)
		{
			throw new ValidationException(
				sprintf('Rating must be a positive number, %d given.', $rating)
			);
		}

		$this->readingEntryId = $readingEntryId;
		$this->rating = $rating;
	}
}
