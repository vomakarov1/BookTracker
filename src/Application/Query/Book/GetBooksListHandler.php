<?php

declare(strict_types=1);

namespace BookTracker\Application\Query\Book;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\DTO\BookDTOAssembler;
use BookTracker\Domain\Entity\Book;
use BookTracker\Domain\Repository\BookRepositoryInterface;

final class GetBooksListHandler
{
	public function __construct(
		private readonly BookRepositoryInterface $bookRepository,
	)
	{
	}

	/** @return array<BookDTO> */
	public function handle(GetBooksListQuery $query): array
	{
		$books = $this->bookRepository->getAll();

		if ($query->category !== null)
		{
			$books = array_filter($books, fn(Book $b) => $b->getCategory() === $query->category);
		}

		if ($query->author !== null)
		{
			$books = array_filter($books, fn(Book $b) => $b->getAuthor() === $query->author);
		}

		return array_values(
			array_map(
				static fn(Book $b) => BookDTOAssembler::fromEntity($b),
				$books,
			),
		);
	}
}
