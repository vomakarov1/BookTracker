<?php

declare(strict_types=1);

namespace BookTracker\Application\DTO;

use BookTracker\Domain\Entity\Book;

final class BookDTOAssembler
{
	public static function fromEntity(Book $book): BookDTO
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
