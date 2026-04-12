<?php

declare(strict_types=1);

namespace BookTracker\Application\Query\Book;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Repository\BookRepositoryInterface;

final class GetBookHandler
{
	public function __construct(
		private readonly BookRepositoryInterface $bookRepository,
	)
	{
	}

	public function handle(GetBookQuery $query): BookDTO
	{
		$book = $this->bookRepository->getById($query->id);

		return $this->toDTO($book);
	}

	private function toDTO(Book $book): BookDTO
	{
		return new BookDTO(
			id: $book->getId(),
			title: $book->getTitle(),
			author: $book->getAuthor(),
			category: $book->getCategory(),
			complexity: $book->getComplexity(),
		);
	}
}
