<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\ReadingEntry;

use BookTracker\Domain\Entity\ReadingEntry;
use BookTracker\Domain\Exception\DuplicateReadingEntryException;
use BookTracker\Domain\Repository\BookRepositoryInterface;
use BookTracker\Domain\Repository\ReadingEntryRepositoryInterface;
use BookTracker\Domain\Repository\UserRepositoryInterface;

final class CreateReadingEntryHandler
{
	public function __construct(
		private readonly UserRepositoryInterface $userRepository,
		private readonly BookRepositoryInterface $bookRepository,
		private readonly ReadingEntryRepositoryInterface $readingEntryRepository,
	)
	{
	}

	public function handle(CreateReadingEntryCommand $command): void
	{
		$user = $this->userRepository->getById($command->userId);
		$book = $this->bookRepository->getById($command->bookId);

		if ($this->readingEntryRepository->existsByUserAndBook($command->userId, $command->bookId))
		{
			throw new DuplicateReadingEntryException(
				sprintf(
					'Reading entry for user "%s" and book "%s" already exists.',
					$command->userId,
					$command->bookId,
				),
			);
		}

		$entry = ReadingEntry::create(id: $command->id, user: $user, book: $book);

		$this->readingEntryRepository->save($entry);
	}
}
