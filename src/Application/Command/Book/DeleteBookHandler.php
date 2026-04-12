<?php

declare(strict_types=1);

namespace BookTracker\Application\Command\Book;

use BookTracker\Domain\Repository\BookRepositoryInterface;

final class DeleteBookHandler
{
	public function __construct(
		private readonly BookRepositoryInterface $bookRepository,
	)
	{
	}

	public function handle(DeleteBookCommand $command): void
	{
		$this->bookRepository->delete($command->id);
	}
}
