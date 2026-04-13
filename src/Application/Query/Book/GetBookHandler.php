<?php

declare(strict_types=1);

namespace BookTracker\Application\Query\Book;

use BookTracker\Application\DTO\BookDTO;
use BookTracker\Application\DTO\BookDTOAssembler;
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
		return BookDTOAssembler::fromEntity(
			$this->bookRepository->getById($query->id),
		);
	}
}
