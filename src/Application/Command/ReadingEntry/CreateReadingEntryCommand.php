<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\ReadingEntry;

use BookTracker\Application\Exception\ValidationException;

final readonly class CreateReadingEntryCommand
{
	public string $userId;
	public string $bookId;

	public function __construct(string $userId, string $bookId)
	{
		if (trim($userId) === '')
		{
			throw new ValidationException('User id must not be empty.');
		}

		if (trim($bookId) === '')
		{
			throw new ValidationException('Book id must not be empty.');
		}

		$this->userId = $userId;
		$this->bookId = $bookId;
	}
}
