<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\ReadingEntry;

use BookTracker\Application\Port\IdGeneratorInterface;
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
		private readonly IdGeneratorInterface $idGenerator,
	)
	{
	}

	public function handle(CreateReadingEntryCommand $command): string
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

		$id = $this->idGenerator->generate();

		$entry = ReadingEntry::create(id: $id, user: $user, book: $book);

		$this->readingEntryRepository->save($entry);

		return $id;
	}
}
