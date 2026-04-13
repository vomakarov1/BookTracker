<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\Book;

use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Exception\DuplicateBookException;
use BookTracker\Domain\Repository\BookRepositoryInterface;

final class CreateBookHandler
{
	public function __construct(
		private readonly BookRepositoryInterface $bookRepository,
	)
	{
	}

	public function handle(CreateBookCommand $command): void
	{
		if ($this->bookRepository->existsByTitle($command->title))
		{
			throw new DuplicateBookException(
				sprintf('Book with title "%s" already exists.', $command->title),
			);
		}

		$book = new Book(
			id: $command->id,
			title: $command->title,
			author: $command->author,
			category: $command->category,
			complexity: $command->complexity,
		);

		$this->bookRepository->save($book);
	}
}
